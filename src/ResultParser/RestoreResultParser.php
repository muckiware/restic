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

class RestoreResultParser extends OutputParser
{
    public static function textParserResult(string $input): ResultEntity
    {
        return new ResultEntity();
    }
}
