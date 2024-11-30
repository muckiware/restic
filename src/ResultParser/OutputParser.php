<?php

namespace MuckiRestic\ResultParser;

use MuckiRestic\Entity\Result\BackupResultEntity;

abstract class OutputParser
{
    abstract public static function textParserBackupResult(string $input): BackupResultEntity;

    public static function transformArray(array $inputArray): array
    {
        $result = [];
        foreach ($inputArray as $item) {
            list($value, $key) = explode(' ', $item, 2);
            $result[$key] = (int)$value;
        }
        return $result;
    }
}
