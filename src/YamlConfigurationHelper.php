<?php

namespace YosypPro\HexagonalStructureCommand;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurationHelper
{
    public static function loadConfiguration($fileName, $index)
    {
        if (!is_file($fileName)) {
            return null;
        }

        $configuration = Yaml::parseFile(
                $fileName
            )[$index] ?? [];

        if (!self::validateConfiguration($configuration, [])) {
            throw new RuntimeException('invalid command configuration');
        }

        return $configuration;
    }

    public static function validateConfiguration($configurationArray, $expectedConfigurationSchema): bool
    {
        // Add method stub for validate yaml file
        return true;
    }
}