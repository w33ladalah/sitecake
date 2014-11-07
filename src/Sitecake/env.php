<?php
namespace Sitecake;

use LogicException;
use RuntimeException;
use League\Flysystem\FilesystemInterface;

class Env {
	
	protected $fs;

	protected $tmp;

	protected $draft;

	protected $backup;

	protected $ignores;

	public function __construct(FilesystemInterface $fs) {
		$this->fs = $fs;

		$this->ensureDirs();

		$this->ignores = array();
		$this->loadIgnorePatterns();
	}

	private function ensureDirs() {
		// check/create directory images
		try {
			if (!$this->fs->ensureDir('images')) {
				throw new LogicException('Could not ensure that the directory /images is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory /images is present and writtable.');
		}
		// check/create files
		try {
			if (!$this->fs->ensureDir('files')) {
				throw new LogicException('Could not ensure that the directory /files is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory /files is present and writtable.');
		}		
		// check/create sitecake-content
		try {
			if (!$this->fs->ensureDir('sitecake-content')) {
				throw new LogicException('Could not ensure that the directory /sitecake-content is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory /sitecake-content is present and writtable.');
		}		
		// check/create sitecake-content/<workid>
		try {
			$work = $this->fs->randomDir('sitecake-content');
			if ($work === false) {
				throw new LogicException('Could not ensure that the work directory in /sitecake-content is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the work directory in /sitecake-content is present and writtable.');
		}	
		// check/create sitecake-content/<workid>/tmp
		try {
			$this->tmp = $this->fs->ensureDir($work . '/tmp');
			if ($this->tmp === false) {
				throw new LogicException('Could not ensure that the directory ' . $work . '/tmp is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory ' . $work . '/tmp is present and writtable.');
		}		
		// check/create sitecake-content/<workid>/draft
		try {
			$this->draft = $this->fs->ensureDir($work . '/draft');
			if ($this->draft === false) {
				throw new LogicException('Could not ensure that the directory ' . $work . '/draft is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory ' . $work . '/draft is present and writtable.');
		}		
		// check/create sitecake-content/<workid>/backup
		try {
			$this->backup = $this->fs->ensureDir($work . '/backup');
			if ($this->backup === false) {
				throw new LogicException('Could not ensure that the directory ' . $work . '/backup is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory ' . $work . '/backup is present and writtable.');
		}	
	}

	private function loadIgnorePatterns() {
		$ignores = array();
		if ($this->fs->has('.scignores')) {
			$this->ignores = preg_split('/\R/', $this->fs->read('.scignores'));
		}
		$ignores = array_merge($this->ignores, array(
			'sitecake/',
			'sitecake-content/'
		));
	}

	/**
	 * Returns the path of the temporary directory.
	 * @return string the tmp dir path
	 */
	public function tmpPath() {
		return $this->tmp;
	}

	/**
	 * Returns the path of the draft directory.
	 * @return string the draft dir path
	 */
	public function draftPath() {
		return $this->draft;
	}

	/**
	 * Returns the path of the backup directory.
	 * @return string the backup dir path
	 */
	public function backupPath() {
		return $this->backup;
	}

	/**
	 * Returns a list of paths of CMS related files from the given
	 * directory. It looks for HTML files, images and uploaded files.
	 * Also, ignore entries from .scignore filter the output list.
	 * 
	 * @param  string $directory the root directory to start search into
	 * @return array            the output paths list
	 */
	public function listScPaths($directory = '') {
		$ignores = $this->ignores;
		return array_filter(array_merge(
			$this->fs->listPatternPaths($directory, '/^[^\/]*\.html?$/'),
			$this->fs->listPatternPaths($directory . '/images', '/^.*sc[0-9a-f]{13}[0-9]{2}\-.*$/'),
			$this->fs->listPatternPaths($directory . '/files', '/^.*sc[0-9a-f]{13}[0-9]{2}\-.*$/')),
			function($path) use ($ignores) {
				foreach ($ignores as $ignore) {
					if ($ignore !== '' && strpos($path, $ignore) === 0) {
						return false;
					}
				}
				return true;
			});
	}

	public function listScPagesPaths($directory = '') {
		$ignores = $this->ignores;
		return array_filter(
			$this->fs->listPatternPaths($directory, '/^[^\/]*\.html?$/'),
			function($path) use ($ignores) {
				foreach ($ignores as $ignore) {
					if ($ignore !== '' && strpos($path, $ignore) === 0) {
						return false;
					}
				}
				return true;
			});		
	}

}
