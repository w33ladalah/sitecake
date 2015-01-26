<?php
namespace Sitecake\Services\Upload;

class Upload {

	protected static $forbidden = array('php', 'php5', 'php4', 'php3', 'phtml', 'phpt');

	protected static $imageExtensions = array('jpeg', 'jpg', 'png', 'gif');

	protected $fs;

	protected $draftPath;

	public function __construct($fs, $draftPath) {
		$this->fs = $fs;
		$this->draftPath = $draftPath;
	}

	public function save($filename) {
		$fileInfo = pathinfo($filename);

		if (!$this->isSafeExtension($fileInfo['extension'])) {
			throw new Exception('Forbidden extension');
		}

		$path = $this->path($fileInfo['filename'], strtolower($fileInfo['extension']), 
			$this->isImage($fileInfo['extension']));

		$res = $this->fs->writeStream($path, fopen("php://input", 'r'));

		return $res ? $path : false;
	}

	protected function isSafeExtension($ext) {
		return !in_array(strtolower($ext), self::$forbidden);
	}

	protected function isImage($ext) {
		return in_array(strtolower($ext), self::$imageExtensions);
	}

	protected function path($name, $extension, $isImage) {
		return $this->draftPath .
			($isImage ? '/images/' : '/files/') .
			str_replace(' ', '_', $name) . '-sc' . uniqid() . '.' . $extension;
	}
}