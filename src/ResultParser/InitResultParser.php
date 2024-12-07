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
use MuckiRestic\Entity\Result\SnapshotsResultEntity;
use MuckiRestic\Entity\Result\FilesDirsResultEntity;

class InitResultParser extends OutputParser
{
    public static function textParserResult(string $input): ResultEntity
    {
        $result = self::getResultFromTextOutput($input);

        $initResultEntity = new ResultEntity();

        if($result && array_key_exists('snapshots', $result)) {

            $snapshotsResultEntity = new SnapshotsResultEntity();
            if(array_key_exists('new', $result['snapshots'])) {
                $snapshotsResultEntity->setNew($result['snapshots']['new']);
            }
            $initResultEntity->setSnapshots($snapshotsResultEntity);
        }

        if($result && array_key_exists('processed', $result)) {
            $initResultEntity->setProcessed($result['processed']);
        }

        return $initResultEntity;
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

        $pattern = '/created restic repository ([a-f0-9]+) at \.\/[^\s]+/';
        if (preg_match($pattern, $input, $matches)) {
            $result['snapshots']['new'] = $matches[1];
        }

        return $result;
    }
}
