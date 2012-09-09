<?php
namespace sitecake;

class util {
	
	/**
	 * Generates unique identifier.
	 * @return string
	 */
	static function id() {
		return sha1(uniqid('', true));
	}
	
	/**
	 * Returns file system path relative to the SC_ROOT. If the
	 * given absolute path does not start with SC_ROOT, the unchanged value
	 * is returned.
	 *
	 * @param string $path absolute path
	 * @return string relative path
	 */
	static function rpath($path) {
		return (strpos($path, $GLOBALS['SC_ROOT'] . '/') === 0) ?
			substr($path, strlen($GLOBALS['SC_ROOT'] . '/')) : $path;
	}
	
	/**
	 * Returns the absolute file system path constructed from the SC_ROOT
	 * and the given relative path.
	 *
	 * @param string $path relative path
	 * @return string absolute path
	 */
	static function apath($path) {
		return $GLOBALS['SC_ROOT'] . '/' . $path;
	}
	
	static function log($text) {
		flock::acquire('log');
		$f = io::fopen($GLOBALS['TEMP'] . '/log.txt', 'ab');
		io::fwrite($f, $text, strlen($text));
		io::fflush($f);
		io::fclose($f);
		flock::release('log');
	}
}