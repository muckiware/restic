<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\ResultParser;

use MuckiRestic\Entity\Result\ResultEntity;

class ForgetResultParser extends OutputParser
{
    public static function textParserResult(string $input): ResultEntity
    {
        $results = self::getResultFromTextOutput($input);

        $checkResultEntity = new ResultEntity();
        foreach ($results['processed'] as $result) {
            $checkResultEntity->addProcessed($result);
        }

        return $checkResultEntity;
    }

    /**
     * @param string $input
     * @return array<mixed>
     */
    protected static function getResultFromTextOutput(string $input): array
    {
        $result = array();
        $inputArray = array_filter(explode("\n", $input), function($value) {
            return $value !== '';
        });

        foreach ($inputArray as $value) {
            $result['processed'][] = $value;
        }

        return $result;
    }
}
