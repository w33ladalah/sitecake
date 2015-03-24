<?php
namespace Sitecake;

use LogicException;
use RuntimeException;
use League\Flysystem\FilesystemInterface;
use Sitecake\Exception\FileNotFoundException;

class Site {
	
	protected $ctx;

	protected $fs;

	protected $tmp;

	protected $draft;

	protected $backup;

	protected $ignores;

	public function __construct(FilesystemInterface $fs, $ctx) {
		$this->ctx = $ctx;
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
			if (!$this->fs->ensureDir('sitecake-temp')) {
				throw new LogicException('Could not ensure that the directory /sitecake-temp is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory /sitecake-temp is present and writtable.');
		}		
		// check/create sitecake-temp/<workid>
		try {
			$work = $this->fs->randomDir('sitecake-temp');
			if ($work === false) {
				throw new LogicException('Could not ensure that the work directory in /sitecake-temp is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the work directory in /sitecake-temp is present and writtable.');
		}	
		// check/create sitecake-temp/<workid>/tmp
		try {
			$this->tmp = $this->fs->ensureDir($work . '/tmp');
			if ($this->tmp === false) {
				throw new LogicException('Could not ensure that the directory ' . $work . '/tmp is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory ' . $work . '/tmp is present and writtable.');
		}		
		// check/create sitecake-temp/<workid>/draft
		try {
			$this->draft = $this->fs->ensureDir($work . '/draft');
			if ($this->draft === false) {
				throw new LogicException('Could not ensure that the directory ' . $work . '/draft is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory ' . $work . '/draft is present and writtable.');
		}

		// check/create sitecake-backup
		try {
			if (!$this->fs->ensureDir('sitecake-backup')) {
				throw new LogicException('Could not ensure that the directory /sitecake-backup is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the directory /sitecake-backup is present and writtable.');
		}
		// check/create sitecake-backup/<workid>
		try {
			$this->backup = $this->fs->randomDir('sitecake-backup');
			if ($work === false) {
				throw new LogicException('Could not ensure that the work directory in /sitecake-backup is present and writtable.');
			}
		} catch (RuntimeException $e) {
			throw new LogicException('Could not ensure that the work directory in /sitecake-backup is present and writtable.');
		}
	}

	private function loadIgnorePatterns() {
		$ignores = array();
		if ($this->fs->has('.scignore')) {
			$this->ignores = preg_split('/\R/', $this->fs->read('.scignore'));
		}
		$ignores = array_merge($this->ignores, array(
			'sitecake/',
			'sitecake-temp/',
			'sitecake-backup/'
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
	 * It ignores entries from .scignore filter the output list.
	 * 
	 * @param  string $directory the root directory to start search into
	 * @param  boolean pagesOnly wether only pages should be returned
	 * @return array            the output paths list
	 */
	public function listScPaths($directory = '', $pagesOnly = false) {
		$ignores = $this->ignores;
		return array_filter(array_merge(
			$this->fs->listPatternPaths($directory, '/^.*\.html?$/'),
			($pagesOnly ? [] : $this->fs->listPatternPaths($directory . '/images', '/^.*\-sc[0-9a-f]{13}[^\.]*\..+$/')),
			($pagesOnly ? [] : $this->fs->listPatternPaths($directory . '/files', '/^.*\-sc[0-9a-f]{13}[^\.]*\..+$/'))),
			function($path) use ($ignores) {
				foreach ($ignores as $ignore) {
					if ($ignore !== '' && strpos($path, $ignore) === 0) {
						return false;
					}
				}
				return true;
			});
	}

	/**
	 * Returns a list of CMS related page file paths from the
	 * given directory.
	 * 
	 * @param  string $directory a directory to read from
	 * @return array            a list of page file paths
	 */
	public function listScPagesPaths($directory = '') {
		return $this->listScPaths($directory, true);	
	}

	/**
	 * Returns a list of draft page file paths.
	 * 
	 * @return array a list of draft page file paths
	 */
	public function listDraftPagePaths() {
		return $this->listScPaths($this->draftPath(), true);
	}

	/**
	 * Starts the site draft out of the public content.
	 * It copies public pages and resources into the draft folder.
	 */
	public function startEdit() {
		if (!$this->draftExists()) {
			$this->startDraft();
		} else {
			$this->cleanupDraft();
		}
	}

	public function restore($version = 0) {

	}

	public function getDefaultPage($directory = '') {
		$paths = $this->listScPagesPaths($directory);
		if (in_array('index.html', $paths)) {
			return new Page($this->fs->read('index.html'));
		} else if (count($paths) > 0) {
			return new Page($this->fs->read($paths[0]));
		} else {
			throw new FileNotFoundException('No HTML page found');
		}
	}

	public function getDefaultPublicPage() {
		return $this->getDefaultPage();
	}

	public function getDefaultDraftPage() {
		return $this->getDefaultPage($this->draftPath());
	}

	public function getPage($uri) {
		$draftPagePaths = $this->listDraftPagePaths();
		$pagePath = $this->draftPath() . '/' . $uri;
		if (in_array($pagePath, $draftPagePaths)) {
			return new Page($this->fs->read($pagePath));
		} else {
			throw new FileNotFoundException($pagePath);
		}
	}

	public function getAllPages() {
		$pages = array();
		$draftPagePaths = $this->listDraftPagePaths();
		foreach ($draftPagePaths as $pagePath) {
			array_push($pages, array('path' => $pagePath, 'page' => new Page($this->fs->read($pagePath))));
		}
		return $pages;
	}

	public function savePage($path, $page) {	
		$this->markDraftDirty();
		$this->fs->update($path, (string)$page);
	}

	public function publishDraft() {
		if ($this->draftExists()) {
			$this->backup();

			$draftResources = $this->draftResources();
			$publicResources = $this->listScPaths();

			$forDeletion = array();
			foreach ($publicResources as $publicResource) {
				if (!in_array($this->draftPath().'/'.$publicResource, $draftResources)) {
					array_push($forDeletion, $publicResource);
				}
			}
			$this->fs->deletePaths($publicResources);			
			$this->fs->copyPaths($draftResources, $this->draftPath(), '/');
			$this->cleanupPublic();
			$this->markDraftClean();
		}
	}

	public function isDraftClean() {
		return !$this->fs->has($this->draftDirtyMarkerPath());
	}

	public function markDraftDirty() {
		if (!$this->fs->has($this->draftDirtyMarkerPath())) {
			$this->fs->write($this->draftDirtyMarkerPath(), '');
		}
	}

	public function markDraftClean() {
		$this->fs->delete($this->draftDirtyMarkerPath());
	}

	protected function draftDirtyMarkerPath() {
		return $this->draftPath().'/draft.drt';
	}

	protected function draftMarkerPath() {
		return $this->draftPath().'/draft.mkr';
	}

	protected function draftExists() {
		return $this->fs->has($this->draftMarkerPath());
	}

	protected function startDraft() {	
		$this->fs->copyPaths($this->listScPaths(), '', $this->draftPath());
		$this->fs->write($this->draftMarkerPath(), '');
		$this->decorateDraft();
	}

	protected function removeDraft() {
		$this->fs->deletePaths($this->listScPaths($this->draftPath()));
		$this->fs->delete($this->draftMarkerPath());
	}

	protected function newBackupContainerPath() {
		$path = $this->backupPath() . '/' . date('Y-m-d-H.i.s') . '-' . substr(uniqid(), -2);
		return $path;
	}

	/**
	 * Remove all backups except for the last recent five.
	 */
	protected function cleanupBackup() {
		$backups = $this->fs->listContents($this->backupPath());
		usort($backups, function($a, $b) {
			if ($a['timestamp'] < $b['timestamp']) {
				return -1;
			} else if ($a['timestamp'] == $b['timestamp']) {
				return 0;
			} else {
				return 1;
			}
		});
		$backups = array_reverse($backups);
		foreach ($backups as $idx => $backup) {
			if ($idx >= $this->ctx['site.number_of_backups']) {
				$this->fs->deleteDir($backup['path']);
			}
		}
	}

	public function backup() {
		$backupPath = $this->newBackupContainerPath();
		$this->fs->createDir($backupPath);
		$this->fs->createDir($backupPath.'/images');
		$this->fs->createDir($backupPath.'/files');
		$this->fs->copyPaths($this->listScPaths(), '', $backupPath);
		$this->cleanupBackup();
	}

	public function editSessionStart() {

	}

	protected function draftBaseUrl() {
		return $this->draftPath() . '/';
	}

	protected function decorateDraft() {
		$draftPagePaths = $this->listDraftPagePaths();
		foreach ($draftPagePaths as $pagePath) {
			$page = new Page($this->fs->read($pagePath));
			$page->ensurePageId();
			$page->normalizeContainerNames();
			$page->prefixResourceUrls($this->draftBaseUrl());
			$this->fs->update($pagePath, (string)$page);
		}
	}

	protected function cleanupPublic() {
		$pagePaths = $this->listScPagesPaths();
		foreach ($pagePaths as $pagePath) {
			$page = new Page($this->fs->read($pagePath));
			$page->removePageId();
			$page->cleanupContainerNames();
			$page->removeMetadata();
			$page->unprefixResourceUrls($this->draftBaseUrl());
			$this->fs->update($pagePath, (string)$page);
		}
	}

	protected function cleanupDraft() {
		$draftResources = $this->draftResources();
		$allResources = $this->listScPaths($this->draftPath());
		foreach ($allResources as $resource) {
			if (!in_array($resource, $draftResources)) {
				$this->fs->delete($resource);
			}
		}
	}

	protected function draftResources() {
		$draftPagePaths = $this->listDraftPagePaths();
		$resources = array_merge(array(), $draftPagePaths);
		foreach ($draftPagePaths as $pagePath) {
			$page = new Page($this->fs->read($pagePath));
			$resources = array_merge($resources, $page->listResourceUrls());
		}
		return array_unique($resources);
	}		
}
