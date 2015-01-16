<?php

namespace Sitecake;

class Services {

	protected $ctx;

	public function __construct($ctx) {
		$this->ctx = $ctx;
	}

	public function load() {
		$this->loadService('\Sitecake\Services\Session\SessionService');
		$this->loadService('\Sitecake\Services\Upload\UploadService');
		$this->loadService('\Sitecake\Services\Image\ImageService');
		$this->loadService('\Sitecake\Services\Pages\PagesService');
		$this->loadService('\Sitecake\Services\Content\ContentService');		
	}

	protected function loadService($class) {
		$name = $class::name();
		$this->ctx[$name] = $this->ctx->share(function($ctx) use ($class) {
			return new $class($ctx);
		});
	}
}