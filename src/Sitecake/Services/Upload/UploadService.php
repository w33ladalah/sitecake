<?php

namespace Sitecake\Services\Upload;

use Sitecake\Services\Service;
use Symfony\Component\HttpFoundation\Response;
use Sitecake\Utils;

class UploadService extends Service {
	const SERVICE_NAME = '_upload';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected static $forbidden = array('php', 'php5', 'php4', 'php3', 'phtml', 'phpt');

	protected $fs;
	protected $draftPath;

	public function __construct($ctx) {
		$this->fs = $ctx['fs'];
		$this->draftPath = $ctx['site']->draftPath();
	}

	public function upload($request) {
		if (!$request->headers->has('x-filename')) {
			return new Response('Filename is missing (header X-FILENAME)', 400);
		}
		$filename = $request->headers->get('x-filename');
		$pathinfo = pathinfo($filename);
		$dpath = Utils::resurl($this->draftPath.'/files', 
			$pathinfo['filename'], null, null, $pathinfo['extension']);

		if (!$this->isSafeExtension($pathinfo['extension'])) {
			return $this->json($request, array('status' => 1, 
				'errMessage' => 'Forbidden file extension '.$pathinfo['extension']), 200);
		}

		$res = $this->fs->writeStream($dpath, fopen("php://input", 'r'));

		if ($res === false) {
			return $this->json($request, array('status' => 1, 
				'errMessage' => 'Unable to upload file '.$pathinfo['filename'].'.'.$pathinfo['extension']), 200);
		} else {
			return $this->json($request, array('status' => 0, 'url' => $res), 200);
		}
	}

	protected function isSafeExtension($ext) {
		return !in_array(strtolower($ext), self::$forbidden);
	}	
}