<?php

namespace MuckiRestic\ResultParser;

use MuckiRestic\Entity\Result\ResultEntity;

abstract class OutputParser
{
    abstract public static function textParserResult(string $input): ResultEntity;

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

    public static function fixJsonOutput(string $processOutput): string
    {
        return '['. preg_replace('/,$/','',str_replace("\n", ',', $processOutput)).']';
    }
}
