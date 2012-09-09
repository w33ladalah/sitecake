<?php
namespace sitecake;

use \phpQuery\phpQuery as phpQuery;
use \phpQuery\DOMDocumentWrapper as DOMDocumentWrapper;
use \WideImage\img as img;
use \Zend\Json\Json as json;

class content {
	/**
	* Saves the given page content into the respective content containers.
	*
	* The response is an array with the following elements:
	* <code>status</code> - int, possible outcomes:
	* -1 - call failed because of an (execution) error
	*  0 - the page content saved
	*
	* <code>errorMessage</code> - string, present if <code>status</code> 
	* 	is -1 or 1
	*
	* @param array $params the page content in the following format:
	* 	scpageid - the sc page id
	* 	sc-content-<name>(sc-repeater-<name>) - the content of the container 
	* 		(or repeater) <name>
	*
	* @return array the service response
	*/
	static function save($params) {
		$id = $params['scpageid'];
		$draft = draft::get($id);
		foreach ($params as $container => $content) {
			if ($container == 'scpageid') continue;
			// remove slashes
			if (get_magic_quotes_gpc())
				$content = stripcslashes($content);	
			$content = base64_decode($content);
			$draft[$container] = $content;		
		}
		draft::update($id, $draft);
		return array('status' => 0);
	}
	
	/**
	 * Publish the site content.
	 *
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - the site published
	 *
	 * <code>errorMessage</code> - string, present if <code>status</code> 
	 * 	is -1 or 1
	 *
	 * @param array $params the page content in the following format:
	 * scpageid - the page name
	 *
	 * @return array the service response
	 */
	static function publish($params) {
		$id = $params['scpageid'];
		$pageFiles = renderer::pageFiles();
		$draft = content::publish_res(draft::get($id));
		foreach ($pageFiles as $pageFile) {
			$html = io::file_get_contents($pageFile);
			if (preg_match('/\\s+scpageid="'.$id.'";/', $html)) {
				$tpl = phpQuery::newDocument($html);
				renderer::normalizeContainerNames($tpl);
				renderer::injectDraftContent($tpl, $draft);
				renderer::cleanupContainerNames($tpl);
				renderer::savePageFile($pageFile, (string)$tpl);
				$repeaters = content::repeaters($draft);
				if (!empty($repeaters)) {
					content::pass_repeaters($pageFiles, $pageFile, $repeaters);
				}				
				draft::delete($id);
				break;
			}
		}
		draft::delete($id);
		return array('status' => 0);
	}
	
	static function publish_res($draft) {
		$mod = array();
		foreach ($draft as $container => $html) {
			preg_match_all('/["\'\\s]' . $GLOBALS['DRAFT_CONTENT_URL'] . 
				'\/([0-9abcdef]{40}\.[^"\'\\s]+)/', $html, $matches);
			$mod[$container] = content::move_draft_res($matches[1], $html);
		}
		return $mod;
	}
	
	static function move_draft_res($names, $html) {
		foreach ($names as $name) {
			$id = substr($name, 0, 40);
			$image = meta::get($id, 'image');
			$spath = $GLOBALS['DRAFT_CONTENT_DIR'] . '/' . $name;
			$dpath = ($image ? 
				$GLOBALS['PUBLIC_IMAGES_DIR'] : $GLOBALS['PUBLIC_FILES_DIR']) .
				'/' . $name;
			$durl = ($image ? 
				$GLOBALS['PUBLIC_IMAGES_URL'] : $GLOBALS['PUBLIC_FILES_URL']) .
				'/' . $id;
			
			$html = preg_replace('/' . $GLOBALS['DRAFT_CONTENT_URL'] . '\/' . 
				$id . '/', $durl, $html);
			
			if (io::file_exists($spath))
				io::rename($spath, $dpath);
		}
		return $html;
	}
	
	static function repeaters($containers) {
		$repeaters = array();
		foreach ($containers as $key => $val) {
			if (preg_match('/^sc\-content\-_rep_.+$/', $key))
				$repeaters[$key] = $val;	
		}
		return $repeaters;
	}
	
	static function pass_repeaters($pageFiles, $currPageFile, $repeaters) {
		foreach ($pageFiles as $pageFile) {
			if ($pageFile == $currPageFile) continue;
			$html = io::file_get_contents($pageFile);
			if (preg_match('/sc\-repeater\-/', $html)) {
				$tpl = phpQuery::newDocument($html);
				renderer::normalizeContainerNames($tpl);
				renderer::injectDraftContent($tpl, $repeaters);
				renderer::cleanupContainerNames($tpl);
				renderer::savePageFile($pageFile, (string)$tpl);
			}			
		}
	}
	
}