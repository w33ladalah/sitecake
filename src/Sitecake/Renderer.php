<?php
namespace Sitecake;

class Renderer {

	protected $sm;

	protected $options;

	protected $site;

	public function __construct($site, $options) {
		$this->site = $site;
		$this->options = $options;
	}
	
	public function loginResponse() {
		return $this->injectLoginDialog($this->site->getDefaultPublicPage());
	}

	public function editResponse($page) {
		$this->site->startEdit();
		return $this->injectEditorCode(
			$this->site->getPage($this->pageUri($page)),
			$this->site->isDraftClean()
		);
	}

	protected function injectLoginDialog($page) {
		$page->appendCodeToHead($this->clientCodeLogin());
		return $page->render();
	}

	protected function pageUri($page) {
		return $page ? $page : 'index.html';
	}

	protected function injectEditorCode($page, $published) {
		$page->appendCodeToHead($this->clientCodeEditor($published));
		return $page->render();
	}

	protected function clientCodeLogin() {
		$globals = 'var sitecakeGlobals = {'.
			"editMode: false, " .
			'serverVersionId: "${version}", ' .
			'phpVersion: "' . phpversion() . '", ' . 
			'serviceUrl:"' . $this->options['SERVICE_URL'] . '", ' .
			'configUrl:"' . $this->options['EDITOR_CONFIG_URL'] . '", ' .
			'forceLoginDialog: true' .
		'};';
				
		return HtmlUtils::wrapToScriptTag($globals) .
			HtmlUtils::scriptTag($this->options['EDITOR_LOGIN_URL']);
	}

	protected function clientCodeEditor($published) {
		$globals = 'var sitecakeGlobals = {'.
			'editMode: true, ' .
			'serverVersionId: "${version}", ' .
			'phpVersion: "' . phpversion() . '", ' . 			
			'serviceUrl: "' . $this->options['SERVICE_URL'] . '", ' .
			'configUrl: "' . $this->options['EDITOR_CONFIG_URL'] . '", ' .				
			'draftPublished: ' . ($published ? 'true' : 'false') .
		'};';
				
		return HtmlUtils::wrapToScriptTag($globals) .
			HtmlUtils::scriptTag($this->options['EDITOR_EDIT_URL']);
	}
}