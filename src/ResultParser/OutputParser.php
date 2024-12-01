<?php

namespace MuckiRestic\ResultParser;

use MuckiRestic\Entity\Result\BackupResultEntity;
use MuckiRestic\Entity\Result\checkResultEntity;

abstract class OutputParser
{
    abstract public static function textParserResult(string $input): BackupResultEntity|CheckResultEntity;

    /**
     * @param array<mixed> $inputArray
     * @return array<mixed>
     */
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
