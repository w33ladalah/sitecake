<?php

namespace Sitecake;

use League\Flysystem\Filesystem;

class Auth implements AuthInterface {
	
	protected $fs;

	protected $credentialsFile;

	protected $credentials;

	public function __construct(Filesystem $fs, $credentialsFile) {
		$this->fs = $fs;
		$this->credentialsFile = $credentialsFile;
		$this->readCredentials();
	}

	public function authenticate($credentials) {
		return ($credentials === $this->credentials);
	}

	protected function readCredentials() {
		$txt = $this->fs->read($this->credentialsFile);
		preg_match_all('/\$credentials\s*=\s*"([^"]+)"/', $txt, $matches);
		$this->credentials = $matches[1][0];
	}

	public function setCredentials($credentails) {
		$this->credentials = $credentails;
		$this->fs->put($this->credentialsFile, '<?php $credentials = "'.$credentails.'"; ?>');
	}	
}