<?php

namespace Sitecake;

use \phpQuery;

class Page {

	protected $sourceHtml;

	protected $doc;

	protected $containers;

	public function __construct($html) {
		$this->sourceHtml = $html;		
		$this->doc = phpQuery::newDocument($html);
	}

	public function __toString() {
		return (string)$this->doc;
	}

	public function render() {
		$this->adjustNavMenu();
		return $this->doc;
	}

	public static function isResourceUrl($url) {
		$re = '/^.*(files|images)\/.*\-sc[0-9a-f]{13}(\-[^\.]+)?\..+$/';

		return HtmlUtils::isRelativeURL($url) &&
				preg_match($re, $url) &&
				(strpos($url, 'javascript:') !== 0) &&
				(strpos($url, '#') !== 0);
	}

	public function prefixResourceUrls($prefix) {
		foreach ($this->containerNodes() as $container) {
			$this->prefixContainerResourceUrls($container, $prefix);
		}
	}

	public function unprefixResourceUrls($prefix) {
		foreach ($this->containerNodes() as $container) {
			$this->unprefixContainerResourceUrls($container, $prefix);
		}		
	}

	public function listResourceUrls() {
		$urls = array();
		foreach ($this->containerNodes() as $container) {
			$urls = array_merge($urls, $this->listContainerResourceUrls($container));
		}
		return $urls;
	}

	public function setContainerContent($containerName, $content) {
		foreach (phpQuery::pq('.' . $containerName, $this->doc) as $node) {
			$container = phpQuery::pq($node, $this->doc);
			$container->html($content);
		}
	}

	public function addMetadata() {
		if (phpQuery::pq('meta[content="sitecake"]', $this->doc)->count() === 0) {
			phpQuery::pq('head', $this->doc)->prepend('<meta name="application-name" content="sitecake"/>');
		}
	}

	public function removeMetadata() {
		phpQuery::pq('meta[content="sitecake"]', $this->doc)->remove();
	}

	public function ensurePageId() {
		$this->addMetadata();
		phpQuery::pq('meta[content="sitecake"]', $this->doc)->attr('data-pageid', Utils::id());
		//HtmlUtils::appendToHead($this->doc,
		//	'<script id="scpageid" type="text/javascript">var scpageid="' . Utils::id() . '";</script>');
	}

	public function pageId() {
		return phpQuery::pq('meta[content="sitecake"]', $this->doc)->attr('data-pageid');
		//foreach (phpQuery::pq('#scpageid', $this->doc) as $node) {
		//	$html = phpQuery::pq($node, $this->doc)->html();
		//	return (preg_match('/\s+scpageid[\s\n]*=[\s\n]*["\']([^"]+)["\']/s', 
		//		$html, $matches)) ? $matches[1] : false;
		//}
		//return false;
	}

	public function removePageId() {
		phpQuery::pq('meta[content="sitecake"]', $this->doc)->removeAttr('data-pageid');
		//foreach (phpQuery::pq('#scpageid', $this->doc) as $node) {
		//	phpQuery::pq($node, $this->doc)->remove();
		//}
	}

	public function appendCodeToHead($code) {
		HtmlUtils::appendToHead($this->doc, $code);		
	}

	protected function prefixContainerResourceUrls($container, $prefix) {
		foreach (phpQuery::pq('a, img',	$container) as $node) {
			HtmlUtils::prefixNodeAttrs($node, 'src,href,srcset', $prefix, function($url) {
				return self::isResourceUrl($url);
			});
		}
	}

	protected function unprefixContainerResourceUrls($container, $prefix) {
		foreach (phpQuery::pq('a, img',	$container) as $node) {
			HtmlUtils::unprefixNodeAttrs($node, 'src,href,srcset', $prefix, function($url) {
				return self::isResourceUrl($url);
			});
		}
	}

	protected function listContainerResourceUrls($container) {
		$urls = array();
		$html = (string)phpQuery::pq($container, $this->doc);
		preg_match_all("/[^\\s\"']*(?:files|images)\\/[^\\s]*\\-sc[0-9a-f]{13}(\-[^\.]+)?\\.[0-9a-zA-Z]+/", 
			$html, $matches);
		foreach ($matches[0] as $match) {
			if (self::isResourceUrl($match)) {
				array_push($urls, $match);	
			}
		}
		return $urls;
	}

	protected function containerNodes() {
		return phpQuery::pq('[class*="sc-content"]', $this->doc);
	}

	public function normalizeContainerNames() {
		foreach ($this->containerNodes() as $node) {
			$container = phpQuery::pq($node, $this->doc);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)sc\-content($|\s)/', $class, $matches)) {
				$container->addClass('sc-content-_cnt_' . uniqid());
			}
		}		
	}
	
	public function cleanupContainerNames() {
		foreach ($this->containerNodes() as $node) {
			$container = phpQuery::pq($node, $this->doc);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)(sc\-content\-_cnt_[^\s]+)/', 
					$class, $matches)) {
				$container->removeClass($matches[2]);
			}
		}		
	}
	
	public function containers() {
		if (!$this->_containers) {
			$this->_containers = array();
			foreach ($this->containerNodes() as $container) {
				preg_match('/(^|\s)(sc-content-[^\s]+)/', 
					$container->getAttribute('class'), $matches);
				// the container is not a repeater
				array_push($this->_containers, $matches[2]);
			}					
		}
		return $this->_containers;
	}
	
	public static function isExternalNavLink($url) {
		return (strpos($url, '/') !== false) || (strpos($url, 'http://') === 0) || 
			(substr($url, -5) !== '.html');
	}

	protected function adjustNavMenu() {
		foreach (phpQuery::pq('ul.sc-nav li a', $this->doc) as $node) {
			$href = $node->getAttribute('href');
			if (!self::isExternalNavLink($href)) {
				$node->setAttribute('href', 'sc-admin.php?page=' . $href);
			}
		}
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
}