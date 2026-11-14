<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Test\Entity;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Entity\Result\ResticResponse\Snapshot;

class SnapshotMappingTest extends TestCase
{
    public function testSnapshotWithoutSummaryAndHostnameMaps(): void
    {
        $json = json_decode('{"time":"2023-01-05T02:00:00.123456789Z","tree":"t","paths":["/srv/a"],'
            .'"id":"1234567890abcdef","short_id":"12345678"}');
        $snapshot = (new \JsonMapper())->map($json, new Snapshot());

        self::assertFalse($snapshot->hasSummary());
        self::assertNull($snapshot->getSummary());
        self::assertSame('', $snapshot->getHostname());
        self::assertSame('', $snapshot->getParent());
        self::assertSame(['/srv/a'], $snapshot->getPaths());
    }

    public function testSnapshotWithoutPathsMaps(): void
    {
        $json = json_decode('{"time":"2023-01-05T02:00:00Z","id":"1234567890abcdef","short_id":"12345678"}');
        $snapshot = (new \JsonMapper())->map($json, new Snapshot());

        self::assertSame([], $snapshot->getPaths());
    }
}
