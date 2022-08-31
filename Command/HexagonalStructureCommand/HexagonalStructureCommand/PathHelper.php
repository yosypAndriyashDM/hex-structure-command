<?php

namespace App\Command\HexagonalStructureCommand;

use ApiPlatform\Core\Exception\RuntimeException;

class PathHelper {

    /**
     * @param $dir
     * @return bool
     */
    public static function createDir($dir): bool
    {
        if (!is_dir($dir) && !mkdir($dir) && !is_dir($dir)) {
            var_dump($dir);
            throw new RuntimeException($dir);
        }

        return true;
    }

    public static function dirExists($dir): bool
    {
        return is_dir($dir);
    }

    public static function getLastDirFromPath($path, $nDirs = 2)
{
    return DIRECTORY_SEPARATOR . implode(
        DIRECTORY_SEPARATOR,
        array_reverse(
            array_slice (
                array_reverse(
                    explode(DIRECTORY_SEPARATOR, $path)
                ), 0, $nDirs
            )
        ));
    }
}