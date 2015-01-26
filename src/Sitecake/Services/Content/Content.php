<?php
namespace Sitecake\Services\Content;

class Content {

	protected $site;

	protected $_pages;

	protected $_containers;

	public function __construct($site) {
		$this->site = $site;
	}

	public function save($data) {
		foreach ($data as $container => $content) {
			// remove slashes
			if (get_magic_quotes_gpc())
				$content = stripcslashes($content);	
			$content = base64_decode($content);
			$this->setContainerContent($container, $content);
		}
		$this->savePages();
		return 0;
	}

	protected function pages() {
		if (!$this->_pages) {
			$this->_pages = $this->site->getAllPages();
		}
		return $this->_pages;
	}

	protected function containers() {
		if (!$this->_containers) {
			$this->initContainers();
		}
		return $this->_containers;
	}

	protected function initContainers() {
		$this->_containers = array();
		$pages = $this->pages();
		foreach ($pages as $page) {
			$pageContainers = $page['page']->containers();
			foreach ($pageContainers as $container) {
				if (array_key_exists($container, $this->_containers)) {
					array_push($this->_containers[$container], $page);
				} else {
					$this->_containers[$container] = array($page);
				}
			}
		}
	}

	protected function setContainerContent($container, $content) {
		$containers = $this->containers();
		if (isset($containers[$container])) {
			foreach ($containers[$container] as $page) {
				$this->setPageDirty($page);
				$page['page']->setContainerContent($container, $content);			
			}	
		}		
	}

	protected function setPageDirty($page) {
		foreach ($this->_pages as &$p) {
			if ($page['path'] === $p['path']) {
				$p['dirty'] = true;
			}
		}
	}

	protected function savePages() {		
		foreach ($this->pages() as $page) {
			if (isset($page['dirty']) && $page['dirty'] === true) {
				$this->site->savePage($page['path'], $page['page']);
			}
		}
	}
}