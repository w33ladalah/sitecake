<?php

namespace Sitecake\Services\Image;

use Sitecake\Services\Service;

class ImageService extends Service {

	const SERVICE_NAME = '_image';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $imageTool;

	public function __construct($ctx) {
		$this->ctx = $ctx;
		$this->imageTool = new ImageTool($ctx['fs'], $ctx['site']->draftPath());
	}

}