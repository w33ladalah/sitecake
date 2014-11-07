<?php

namespace Sitecake;

use League\Flysystem\Filesystem;

class Auth implements AuthInterface {
	
	protected $fs;

	protected $credentialsFile;

	public function __construct(Filesystem $fs, $credentialsFile) {
		$this->fs = $fs;
		$this->credentialsFile = $credentialsFile;
	}

	public function authenticate($credentails) {
		return ($credentails === $GLOBALS['credentials']);
	}

	public function setCredentials($credentails) {
		$GLOBALS['credentials'] = $credentails;
		$this->fs->put($this->credentialsFile, '<?php $credentials = "'.$credentails.'"; ?>');
	}	
}