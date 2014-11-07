<?php

namespace Sitecake;

use League\Flysystem\Filesystem;

class FileLock {
	
	protected $fs;

	protected $tmpdir;

	public function __construct(Filesystem $fs, $tmpdir) {
		$this->fs = $fs;
		$this->tmpdir = $tmpdir;
	}

	public function set($name, $timeout = 0) {
		$t = ($timeout == 0) ? 0 : (string)(round(microtime(true) * 1000) + $timeout);
		$this->fs->put($this->path($name), $t);
	}

	public function remove($name) {
		$path = $this->path($name);
		if ($this->fs->has($path)) {
			$this->fs->delete($path);
		}
	}
	
	public function exists($name) {
		$file = $this->path($name);
		if ($this->fs->has($file)) {
			if ($this->timedout($file)) {
				$this->fs->delete($file);
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	private function timedout($lock) {
		$timeout = intval($this->fs->read($lock));
		return $timeout == 0 ? 
			false : ($timeout - round(microtime(true) * 1000)) < 0;
	}
	
	private function path($name) {
		return $this->tmpdir . '/' . $name . '.lock';
	}	
}