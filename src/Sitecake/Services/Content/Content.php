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
		foreach ($data->keys() as $container) {
			$content = $data->get($container);
			// remove slashes
			if (get_magic_quotes_gpc())
				$content = stripcslashes($content);	
			$content = base64_decode($content);

			$pages->setContainerContent($container, $content);
		}
		$pages->savePages();
	}

	protected function pages() {
		if (!$this->_pages) {
			$this->_pages = $this->site->getAllPages();
		}
		return $_pages;
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

	}

	protected function savePages() {
		
	}
}