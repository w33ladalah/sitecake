<?php
namespace Sitecake;

use \phpQuery;
use \Exception as Exception;

class Renderer {

	protected $sm;

	protected $options;

	public function __constructor(SessionManagerInterface $sm, $options) {
		$this->sm = $sm;
		$this->options = $options;
	}

	public function process() {
		return $sm->isLoggedIn() ?
			$this->loginResponse() : $this->editResponse();
	}
	
	protected function loginResponse() {
		return $this->injectLoginDialog($this->getDefaultPublicPage());
	}

	protected function getDefaultPublicPage() {
		return "<html><head></head><body>It works</body></html>";
	}

	protected function injectLoginDialog($html) {
		return (string)HtmlUtils::appendToHead($html, $this->clientCodeLogin());
	}

	protected function clientCodeLogin() {
		$globals = "var sitecakeGlobals = {".
			"editMode: false, " .
			"serverVersionId: 'SiteCake CMS ${version}', " .
			"sessionServiceUrl:'" . $this->options['SERVICE_URL'] . "', " .
			"configUrl:'" . $this->options['CONFIG_URL'] . "', " .
			"forceLoginDialog: true" .
		"};";
				
		return Utils::wrapToScriptTag($globals) .
			Utils::scriptTag($this->options['SITECAKE_EDITOR_LOGIN_URL']);
	}

	/**
	 * 
	 * Enter description here ...
	 * @param Request $req
	 */
	static function response($req) {
		$pageFiles = renderer::pageFiles();		
		$pageUri = renderer::pageUri($req->getQuery());
		if (array_key_exists($pageUri, $pageFiles)) {
			renderer::normalize_pages($pageFiles);
			return renderer::assemble(
				$pageFiles[$pageUri], 
				!renderer::isLoggedin());
		} else {
			return http::notFoundResponse($req->getBasePath() . '/' . $pageUri);
		}
	}
	
	static function pageUri($params) {
		return isset($params['page']) ? $params['page'] : 'index.html';
	}
		
	static function isExternalLink($url) {
		return strpos($url, '/') || strpos($url, 'http://') || 
			(substr($url, -5) != '.html');
	}
	
	static function pageFiles() {
		$pages = pages::page_files();
	
		if ($pages === false || empty($pages)) {
			throw new Exception(
				resources::message('NO_PAGE_EXISTS', SC_ROOT));
		}
		
		if (!array_key_exists('index.html', $pages)) {
			throw new Exception(
				resources::message('INDEX_PAGE_NOT_EXISTS', SC_ROOT));
		}
				
		return $pages;
	}
	
	static function load_page($path) {
		if (!io::is_readable($path))
			throw new Exception(resources::message('PAGE_NOT_EXISTS', $path));
		return io::file_get_contents($path);
	}
	
	static function save_page($path, $content) {
		io::file_put_contents($path, $content);
	}
	
	static function normalize_pages($pages) {
		array_walk($pages, function($path) {
			renderer::normalize_page_id($path);
		});
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $pageFile
	 * @param boolean $isLogin
	 * @return Response
	 */
	static function assemble($pageFile, $isLogin) {
		$html = renderer::load_page($pageFile);
		$doc = phpQuery::newDocument($html);
		renderer::adjustNavMenu($doc);
		renderer::normalizeContainerNames($doc);
		if (!$isLogin) {
			renderer::injectDraftContent($doc, 
				draft::get(pages::page_id($html)));
		}
		renderer::injectClientCode($doc, $html, $isLogin);
		return http::response($doc);
	}
	
	static function normalizeContainerNames($tpl) {
		$cnt = 0;
		foreach (phpQuery::pq('[class*="sc-content"], [class*="sc-repeater-"]', 
				$tpl) as $node) {
			$container = phpQuery::pq($node, $tpl);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)sc\-content($|\s)/', $class, $matches)) {
				$container->addClass('sc-content-_cnt_' . $cnt++);
			} else if (preg_match('/(^|\s)sc\-repeater-([^\s]+)($|\s)/', 
					$class, $matches)) {
				$container->addClass('sc-content-_rep_' . $matches[2]);
			}
		}
		return $tpl;		
	}
	
	static function cleanupContainerNames($tpl) {
		foreach (phpQuery::pq('[class*="sc-content-"], [class*="sc-repeater-"]',
				$tpl) as $node) {
			$container = phpQuery::pq($node, $tpl);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)(sc\-content\-(_cnt_|_rep_)[^\s]+)/', 
					$class, $matches)) {
				$container->removeClass($matches[2]);
			}
		}
		return $tpl;		
	}
	
	static function containers($tpl) {
		$containers = array();
		foreach (phpQuery::pq('[class*="sc-content-"], [class*="sc-repeater-"]',
				$tpl) as $node) {
			$cNode = phpQuery::pq($node, $tpl);
			if (preg_match('/(^|\s)(sc-content-_rep_[^\s]+)/', $cNode->attr('class'),
					$matches)) {
				$containers[$matches[2]] = true;
			}
			else {
				preg_match('/(^|\s)(sc-content-[^\s]+)/', 
					$cNode->attr('class'), $matches);
				$containers[$matches[2]] = false;
			}
		}
		return $containers;			
	}
	
	static function adjustNavMenu($tpl) {
		foreach (phpQuery::pq('ul.sc-nav li a', $tpl) as $navNode) {
			$node = phpQuery::pq($navNode, $tpl);
			$href = $node->attr('href');
			if (!renderer::isExternalLink($href)) {
				$node->attr('href', 'sc-admin.php?page=' . $href);
			}
		}
		return $tpl;
	}
	
	static function injectClientCode($tpl, $html, $isLogin) {
		$id = pages::page_id((string)$tpl);
		phpQuery::pq('head', $tpl)->append(
			renderer::clientCode($isLogin, draft::exists($id)));	
		return $tpl;
	}
	
	static function clientCode($isLogin, $isDraft) {
		return $isLogin ? 
			$this->clientCodeLogin() : $this->clientCodeEdit($isDraft);
	}
	

	
	static function clientCodeEdit($isDraft) {
		$globals = "var sitecakeGlobals = {".
			"editMode: true, " .
			"sessionId: '<session id>', " .
			"serverVersionId: 'SiteCake CMS ${version}', " .
			"sessionServiceUrl:'" . SERVICE_URL . "', " .
			"uploadServiceUrl:'" . SERVICE_URL . "', " .
			"contentServiceUrl:'" . SERVICE_URL . "', " .
			"configUrl:'" . CONFIG_URL . "', " .				
			"draftPublished: " . ($isDraft ? 'false' : 'true') .
		"};";
				
		return
			'<meta http-equiv="X-UA-Compatible" content="chrome=1">' .
			renderer::wrapToScriptTag($globals) .
			renderer::scriptTag(SITECAKE_EDITOR_EDIT_URL);
	}
	

	
	static function injectDraftContent($tpl, $content) {
		$containers = renderer::containers($tpl);
		foreach ($containers as $container => $repeater) {
			if (array_key_exists($container, $content)) {
				renderer::setContent($tpl, $container, $content[$container]);
			}
		}
		return $tpl;
	}
	
	/**
	 * Ensures that the given page has the <code>scpageid</code>. If the 
	 * <code>scpageid</code> is not present, a new ID value will be generated
	 * and the page will be modified.
	 *
	 * @param string $path the page file path
	 * @param string $html the page html (optional), if the html is ommited, the
	 * 	page html will be loaded using the given path
	 */
	static function normalize_page_id($path, $html = null) {
		$html = ($html == null) ? renderer::load_page($path) : $html;
		$id = pages::page_id($html);
		if (!$id) {
			$code = renderer::wrapToScriptTag('var scpageid="'.util::id().'";');
			renderer::save_page($path, 
				preg_replace('/<\/head([^>]*)>/i', $code.'</head\1>', $html)); 
		}
	}
	
	static function setContent($tpl, $container, $content) {
		phpQuery::pq('.' . $container, $tpl)->html($content);
	}
	
	static function purge() {
		$used = renderer::used_references();
		foreach (meta::ids() as $id) {
			if (!in_array($id, $used) && !meta::find('oid', $id)) {
				renderer::purge_res($id);
			}
		}
	}
	
	static function purge_res($id) {
		$meta = meta::get($id);
		meta::remove($id);
		$path = util::apath($meta['path']);
		if (io::file_exists($path)) io::unlink($path);
		$fpath = PUBLIC_FILES_DIR . '/' . $meta['name'];
		if (io::file_exists($fpath)) io::unlink($fpath);
		$ipath = PUBLIC_IMAGES_DIR . '/' . $meta['name'];
		if (io::file_exists($ipath)) io::unlink($ipath);		
	}
	
	static function used_references() {
		$refs = array();
		foreach (renderer::pageFiles() as $path) {
			$refs = array_merge($refs, 
				renderer::extract_refs(io::file_get_contents($path)));
		}
		
		foreach (draft::getAll(true) as $drf) {
			$refs = array_merge($refs, renderer::extract_refs($drf));
		}
		return $refs;
	}
	
	static function extract_refs($text) {
		preg_match_all('/\/([0-9abcdef]{40})\./', $text, $matches);
		return $matches[1];
	}

}