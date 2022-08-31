<?php

namespace YosypPro\HexagonalStructureCommand;

class StringHelper {

    public const SPACE_DELIMITER = ' ';

    public static function toLowerCamelCase($string, $delimiter = self::SPACE_DELIMITER)
    {
        $stringParts = explode($delimiter, $string);

        if (count($stringParts) < 1) {
            return $string;
        }

        $result = strtolower($stringParts[0]);
        unset($stringParts[0]);

        if(count($stringParts) < 1) {
            return $result;
        }

        foreach ($stringParts as $stringPart) {
            $result .= ucfirst($stringPart);
        }

        return $result;
    }
}