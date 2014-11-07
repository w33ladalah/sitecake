<?php
namespace Sitecake\Filesystem;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

class ListPatternPaths implements PluginInterface {
    protected $fs;

    public function setFilesystem(FilesystemInterface $filesystem) {
        $this->fs = $filesystem;
    }

    public function getMethod() {
        return 'listPatternPaths';
    }

    /**
     * Lists filesystem paths that match the given pattern.
     *
     * @param  string $directory directory to list 
     * @param  string $pattern regexp patterns to match paths
     * @param  bool $recursive should the path listing is recursive 
     * @return array
     */
    public function handle($directory = '', $pattern, $recursive = false) {
        $existingPaths = $this->fs->listPaths($directory, $recursive);
        $matchedPaths = array();
        foreach ($existingPaths as $path) {
            if (preg_match($pattern, $path) === 1) {
                $matchedPaths[] = $path;
            }
        }
        return $matchedPaths;
    }
}