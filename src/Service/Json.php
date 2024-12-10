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
namespace MuckiRestic\Service;

use MuckiRestic\Exception\ActionException;
use MuckiRestic\Exception\JsonException;

class Json
{
    public static function encode($value, int $options = 0, int $depth = 512) : string
    {
        $json = json_encode($value, $options, $depth);

        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw JsonException::encoding($value, $error);
        }

        return $json;
    }

    public static function decode(string $json, bool $asArray=false, int $depth=512, int $options=0)
    {
        $decoded = json_decode($json, $asArray, $depth, $options);

        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw JsonException::decoding($json, $error);
        }

        return $decoded;
    }
}
