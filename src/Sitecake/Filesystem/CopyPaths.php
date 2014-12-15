<?php
namespace Sitecake\Filesystem;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class CopyPaths implements PluginInterface {
    protected $fs;

    public function setFilesystem(FilesystemInterface $filesystem) {
        $this->fs = $filesystem;
    }

    public function getMethod() {
        return 'copyPaths';
    }

    /**
     * Copies the given list of file paths, relative to the
     * given source path, to the given destination path.
     * 
     * @param  array $paths a list of paths to be copied.
     * @param  string $spath the source path
     * @param  string $dpath the destination path
     */
    public function handle($paths, $spath, $dpath) {
        foreach ($paths as $path) {
            $newpath = $dpath . '/' . substr($path, strlen($spath));            
            $this->fs->copy($path, $newpath);
        }
    }
}