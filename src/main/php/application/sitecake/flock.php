<?php
namespace sitecake;

/**
 * Inter-thread synchronization mechanism.
 */
class flock {
	static $_handlers = array();
	
	/**
	 * Acquires a named lock. The lock is implemented using a temporary
	 * file and the fopen x mode to obtain exclusive write access to it.
	 * 
	 * @param string $lockId the lock name/id
	 * @param boolean $block (optional) signals if the method should block the
	 * 	caller trying to acquire the lock (true) or just try once and return
	 * @return boolean true if lock has been acquired
	 */
	static function acquire($lockId, $block = true) {
		if (!isset(flock::$_handlers[$lockId])) {
			$file = flock::_fname($lockId);
			$handler = false;
			do {
				if (!io::file_exists($file) || @io::unlink($file)) {
					$handler = @io::fopen($file, "x");
				}
				if (false === $handler && $block) {
					usleep(10000);
				} else {
					flock::$_handlers[$lockId] = $handler;
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
		if (isset(flock::$_handlers[$lockId])) {
			@io::fclose(flock::$_handlers[$lockId]);
			@io::unlink(flock::_fname($lockId));
			unset(flock::$_handlers[$lockId]);
		}		
	}
	
	/**
	 * Constructs the filesystem path for the given lock ID.
	 * 
	 * @param string $lockId
	 * @return the respective path 
	 */
	static function _fname($lockId) {
		return TEMP_DIR . '/' . $lockId . '.flock';
	}
}