<?php
namespace Sitecake\Filesystem;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class RandomDirectory implements PluginInterface {
    protected $fs;

    public function setFilesystem(FilesystemInterface $filesystem) {
        $this->fs = $filesystem;
    }

    public function getMethod() {
        return 'randomDir';
    }

    /**
     * Returns an existing or a newly created random directory in the given directory. The 
     * random directory is a directory with a random name of the certain pattern. The directory name 
     * pattern used is /r[0-9a-f]{13}/.
     * 
     * @param  string $directory directory that the random directory should be in 
     * @return bool|string            returns the random directory path if operation succeeded, false otherwise
     */
    public function handle($directory) {
        $existingPaths = $this->fs->listPatternPaths($directory, '/^.*\/r[0-9a-f]{13}$/');
        if (count($existingPaths) > 0) {
            return $existingPaths[0];
        } else {
            $directory = $directory . '/' . uniqid('r');
            return $this->fs->ensureDir($directory) ? $directory : false;            
        }
    }
}