<?php
namespace Sitecake\Services\Upload;

use Sitecake\Utils;

class Upload {

	protected static $forbidden = array('php', 'php5', 'php4', 'php3', 'phtml', 'phpt');

	protected $fs;

	public function __construct($fs) {
		$this->fs = $fs;
	}

	public function save($path) {
		$pinfo = Utils::resurlinfo($path);

		if (!$this->isSafeExtension($pinfo['ext'])) {
			throw new Exception('Forbidden file extension '.$fileInfo['extension']);
		}

		$res = $this->fs->writeStream($path, fopen("php://input", 'r'));
		return $res ? $path : false;
	}

	protected function isSafeExtension($ext) {
		return !in_array(strtolower($ext), self::$forbidden);
	}
}