<?php

namespace YosypPro\HexagonalStructureCommand;

use RuntimeException;

class PathHelper {

    /**
     * @param $dir
     * @return bool
     */
    public static function createDir($dir): bool
    {
        $routePath = DIRECTORY_SEPARATOR;
        $dirComponents = explode('/', str_replace('\\', '/', $dir));

        foreach ($dirComponents as $current) {
            $routePath .= ($current . DIRECTORY_SEPARATOR);

            if (!is_dir($routePath) && !mkdir($routePath) && !is_dir($routePath)) {
                throw new RuntimeException($dir);
            }
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