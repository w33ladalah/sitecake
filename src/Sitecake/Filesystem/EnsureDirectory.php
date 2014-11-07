<?php
namespace Sitecake\Filesystem;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class EnsureDirectory implements PluginInterface {
    protected $fs;

    public function setFilesystem(FilesystemInterface $filesystem) {
        $this->fs = $filesystem;
    }

    public function getMethod() {
        return 'ensureDir';
    }

    /**
     * Ensures that the specified directory exists by creating it
     * if not exists already.
     * 
     * @param  string $directory directory path
     * @return bool|string            returns the path of the directory if operation succeeded, false otherwise
     */
    public function handle($directory) {
        if ($this->fs->has($directory)) {
            return $this->fs->get($directory)->isDir() ? $directory : false;
        } else {
            return $this->fs->createDir($directory) ? $directory : false;
        }
    }
}