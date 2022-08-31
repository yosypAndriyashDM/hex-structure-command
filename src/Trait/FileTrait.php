<?php

namespace YosypPro\HexagonalStructureCommand\Trait;

use YosypPro\HexagonalStructureCommand\PathHelper;
use RuntimeException;

trait FileTrait {

    // Centralize all dirs-creation calls here to allow add or remove output comments (or logs)
    private function createDir($dir): void
    {
        // With this class method we can ask user if he want to overwrite directory or something else...
        if (PathHelper::dirExists($dir)) {
            $this->writeComment($dir . ' directory already exists');
        } else {
            PathHelper::createDir($dir);
            $this->writeSuccess($dir . ' directory created');
        }
    }

    private function createFile($fileName, $content = '',  $forceReplace = false): bool|string
    {
        try {
            if (!file_exists($fileName)) {
                return $this->touchFile($fileName, $content);
            }

            if (file_exists($fileName) && $forceReplace === false) {
                $shortFile = '...' . PathHelper::getLastDirFromPath($fileName, 7);
                $this->writeError('File ' . $shortFile . ' already exists, do you want to replace it? (This will erase current file content)');
                $this->writeLine();
                $this->writeLine('Select option:');
                $this->writeLine('1: Yes, replace it');
                $this->writeLine('2: No, keep this file and continue');

                $createFile = (int) $this->getUserInputRequest();

                if (!in_array($createFile, [1, 2])) {
                    throw new RuntimeException();
                }

                if ($createFile === 1) {
                    return $this->createFile($fileName, $content, true);
                }
            }

            if (file_exists($fileName) && $forceReplace === true) {
                return $this->touchFile($fileName, $content);
            }

        } catch (RuntimeException) {
            $this->writeError('Invalid response:');
            $this->createFile($fileName, $content);
        }

        return $content;
    }

    private function touchFile($fileName, $content = ''): bool
    {
        $operation = file_exists($fileName) ? ' overwritten' : ' created';

        try {
            $fileStream = fopen($fileName, 'w+');

            fwrite($fileStream, $content);
            fclose($fileStream);

            $this->writeSuccess('...' . PathHelper::getLastDirFromPath($fileName, 7) . $operation);
        } catch (RuntimeException $exception) {

            $this->writeError('...' . PathHelper::getLastDirFromPath($fileName, 7) . ' writing error');
        }


        return is_file($fileName);
    }

    private function getTemplateContent($templatePath): string
    {
        $templatePath = $this->commandPath . $templatePath;
        return file_get_contents($templatePath);
    }
}