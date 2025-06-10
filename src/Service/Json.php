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
    /**
     * @param mixed $value
     * @param int $options
     * @param int<1, max> $depth
     * @return false|string
     */
    public static function encode(mixed $value, int $options = 0, int $depth = 512) : false|string
    {
        $json = json_encode($value, $options, $depth);

        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw JsonException::encoding($value, $error);
        }

        return $json;
    }

    /**
     * @param string $json
     * @param bool $asArray
     * @param int<1, max> $depth
     * @param int $options
     * @return mixed
     */
    public static function decode(string $json, bool $asArray=false, int $depth=512, int $options=0): mixed
    {
        $decoded = json_decode($json, $asArray, $depth, $options);

        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw JsonException::decoding($json, $error);
        }

        return $decoded;
    }
}
