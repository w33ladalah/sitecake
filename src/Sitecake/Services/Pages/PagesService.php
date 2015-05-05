<?php

namespace Sitecake\Services\Pages;

use Sitecake\Services\Service;

class PagesService extends Service {

	const SERVICE_NAME = '_pages';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $pages;

	public function __construct($ctx) {
		$this->ctx = $ctx;
		//$this->pages = new Pages($ctx['site'], $ctx);
	}

	public function pages($req) {
		return $this->json($req, array('status' => 0, 'pages' => []), 200);
	}

}