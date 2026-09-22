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
namespace MuckiRestic\Test\Library;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Configuration;
use MuckiRestic\Exception\InvalidConfigurationException;

class ConfigurationValidationTest extends TestCase
{
    /**
     * @return array<string, array{0: callable(Configuration, string): void}>
     */
    public static function optionLikeSetterProvider(): array
    {
        return [
            'setBinaryPath'     => [static fn (Configuration $c, string $v) => $c->setBinaryPath($v)],
            'setRepositoryPath' => [static fn (Configuration $c, string $v) => $c->setRepositoryPath($v)],
            'setBackupPath'     => [static fn (Configuration $c, string $v) => $c->setBackupPath($v)],
            'setRestoreTarget'  => [static fn (Configuration $c, string $v) => $c->setRestoreTarget($v)],
            'setRestoreItem'    => [static fn (Configuration $c, string $v) => $c->setRestoreItem($v)],
            'setSnapshotId'     => [static fn (Configuration $c, string $v) => $c->setSnapshotId($v)],
            'addSnapshotId'     => [static fn (Configuration $c, string $v) => $c->addSnapshotId($v)],
            'setSnapshotIds'    => [static fn (Configuration $c, string $v) => $c->setSnapshotIds([$v])],
            'setHostName'       => [static fn (Configuration $c, string $v) => $c->setHostName($v)],
            'setGroupBy'        => [static fn (Configuration $c, string $v) => $c->setGroupBy($v)],
            'setTag'            => [static fn (Configuration $c, string $v) => $c->setTag($v)],
            'setTags'           => [static fn (Configuration $c, string $v) => $c->setTags([$v])],
            'setAwsS3Endpoint'  => [static fn (Configuration $c, string $v) => $c->setAwsS3Endpoint($v)],
        ];
    }

    /**
     * @param callable(Configuration, string): void $setter
     */
    #[DataProvider('optionLikeSetterProvider')]
    public function testRejectsValueStartingWithDash(callable $setter): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $setter(Backup::create(), '--exclude=/etc');
    }

    /**
     * @param callable(Configuration, string): void $setter
     */
    #[DataProvider('optionLikeSetterProvider')]
    public function testAcceptsOrdinaryValue(callable $setter): void
    {
        $setter(Backup::create(), 'plain-value');
        $this::assertTrue(true, 'setter accepted an ordinary value');
    }

    public function testSetSnapshotIdAcceptsNull(): void
    {
        $configuration = Backup::create();
        $configuration->setSnapshotId(null);
        $this::assertNull($configuration->getSnapshotId());
    }

    public function testSetAwsS3EndpointAcceptsNull(): void
    {
        $configuration = Backup::create();
        $configuration->setAwsS3Endpoint(null);
        $this::assertNull($configuration->getAwsS3Endpoint());
    }
}
