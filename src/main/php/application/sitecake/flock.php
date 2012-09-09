<?php
namespace sitecake;

/**
 * A thread-safe locking mechanism.
 */
class flock {
	static $handlers = array();
	
	/**
	 * Acquires a named lock. The lock is implemented using a temporary
	 * file and the fopen x mode to obtain exclusive write access to it.
	 * 
	 * @param string $lockId the lock name/id
	 * @param boolean $block (optional) signals it the method should block the
	 * 	caller trying to acquire the lock (true) or just try once and return
	 * @return boolean true if lock has been acquired
	 */
	static function acquire($lockId, $block = true) {
		if (!isset(flock::$handlers[$lockId])) {
			$file = flock::_fname($lockId);
			$handler = false;
			do {
				if (!io::file_exists($file) || @io::unlink($file)) {
					$handler = @io::fopen($file, "x");
				}
				if (false === $handler && $block) {
					usleep(10000);
				} else {
					flock::$handlers[$lockId] = $handler;
				}
			} while (false === $handler && !$block);
		}
		return ($handler !== false);
	}
	
	/**
	 * Releses a previously acquired named lock.
	 * 
	 * @param string $lockId the lock name/id
	 */
	static function release($lockId) {
		if (isset(flock::$handlers[$lockId])) {
			@io::fclose(flock::$handlers[$lockId]);
			@io::unlink(flock::_fname($lockId));
			unset(flock::$handlers[$lockId]);
		}		
	}
	
	static function _fname($lockId) {
		return $GLOBALS['TEMP'] . '/' . $lockId . '.flock';
	}
}