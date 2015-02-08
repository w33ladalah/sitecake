<?php

namespace Sitecake\Services\Upload;

use Sitecake\Services\Service;
use Symfony\Component\HttpFoundation\Response;

class UploadService extends Service {
	const SERVICE_NAME = '_upload';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $uploader;

	public function __construct($ctx) {
		$this->ctx = $ctx;
		$this->uploader = new Upload($ctx['fs']);
	}

	public function upload($request) {
		if (!$request->headers->has('x-filename')) {
			return new Response('Filename is missing (header X-FILENAME)', 400);
		}
		$filename = $request->headers->get('x-filename');
		$pathinfo = pathinfo($filename);
		$dpath = Utils::resurl($this->ctx['site']->draftPath().'/files', 
			$pathinfo['filename'], null, null, $pathinfo['extension']);
		$res = $this->uploader->save($dpath);
		if ($res === false) {
			return $this->json($request, array('status' => 1, 'errMessage' => 'Unable to upload file'), 200);
		} else {
			return $this->json($request, array('status' => 0, 'url' => $res), 200);
		}
	}
}