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
namespace MuckiRestic\Test\Service;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Service\Json;
use MuckiRestic\Exception\JsonException;

class JsonTest extends TestCase
{
    public function testJsonDecode()
    {
        $json = Json::decode('{"key":"value"}', true);
        $this->assertIsArray($json, 'json decode should be returned an array');
        $this->assertSame(['key' => 'value'], $json, 'Should be an valid json array');
    }

    public function testJsonEncode()
    {
        $json = Json::encode(['key' => 'value']);
        $this->assertIsString($json, 'json decode should be returned an array');
        $this->assertSame('{"key":"value"}', $json, 'Should be an valid json string');
    }

    public function testJsonDecodeSyntaxError()
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage(
            'JSON Syntax error'
        );
        $json = Json::decode('{key value}', true);

    }
}
