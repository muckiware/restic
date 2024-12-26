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

class VersionResultParser extends OutputParser
{
    public static function textParserResult(string $input): ResultEntity
    {
        $result = self::getResultFromTextOutput($input);

        $versionResultEntity = new ResultEntity();

        if($result && array_key_exists('processed', $result)) {
            $versionResultEntity->setProcessed($result['processed']);
        }

        return $versionResultEntity;
    }

    /**
     * @param string $input
     * @return array<mixed>
     */
    protected static function getResultFromTextOutput(string $input): array
    {
        return array('version' => self::extractVersion($input));
    }

    public static function extractVersion(string $input): ?string
    {
        if (preg_match('/restic\s+([\d.]+)/', $input, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
