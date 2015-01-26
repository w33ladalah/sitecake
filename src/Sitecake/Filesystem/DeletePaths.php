<?php
namespace Sitecake\Filesystem;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class DeletePaths implements PluginInterface {
    protected $fs;

    public function setFilesystem(FilesystemInterface $filesystem) {
        $this->fs = $filesystem;
    }

    public function getMethod() {
        return 'deletePaths';
    }

    /**
     * Deletes the given list of file paths.
     * 
     * @param  array $paths a list of paths to be deleted. 
     */
    public function handle($paths) {
        foreach ($paths as $path) {
            $this->fs->delete($path);
        }
    }
}