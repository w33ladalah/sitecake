<?php

namespace Sitecake\Services\Upload;

use Sitecake\Services\Service;

class UploadService extends Service {
	const SERVICE_NAME = '_upload';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $uploader;

	public function __construct($ctx) {
		$this->ctx = $ctx;
		$this->uploader = new Upload($ctx['fs'], $ctx['site']->draftPath());
	}

	public function upload($request) {
		$filename = $request->headers('');
		$res = $this->uploader->save($filename);
		return $res ? 200 : 500;
	}
}