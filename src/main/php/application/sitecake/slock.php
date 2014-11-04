<?php
namespace Sitecake;

class slock {
	static function create($name, $timeout = 0) {
		io::file_put_contents(slock::path($name), $timeout);
	}
	
	static function reset($name) {
		$path = slock::path($name);
		if (io::file_exists($path)) {
			io::touch(slock::path($name));
		}		
	}
	
	static function remove($name) {
		$path = slock::path($name);
		if (io::file_exists($path)) {
			io::unlink($path);
		}
	}
	
	static function exists($name) {
		$file = slock::path($name);
		if (io::file_exists($file)) {
			if (slock::timedout($file)) {
				slock::remove($file);
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	static function timedout($lock) {
		$timeout = io::file_get_contents($lock);
		return $timeout == 0 ? 
			false : (io::filemtime($lock) + $timeout) < time();
	}
	
	static function path($name) {
		return TEMP_DIR . '/' . $name . '.lock';
	}
}