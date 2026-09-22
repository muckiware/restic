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
namespace MuckiRestic\Test\Library\CommandLine;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Backup as BackupClient;
use MuckiRestic\Library\Configuration;
use MuckiRestic\Library\CommandLine\Commands\Backup;
use MuckiRestic\Library\CommandLine\Commands\BackupAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Check;
use MuckiRestic\Library\CommandLine\Commands\CheckAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Forget;
use MuckiRestic\Library\CommandLine\Commands\ForgetAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Init;
use MuckiRestic\Library\CommandLine\Commands\InitAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\ListObjects;
use MuckiRestic\Library\CommandLine\Commands\Prune;
use MuckiRestic\Library\CommandLine\Commands\PruneAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Restore;
use MuckiRestic\Library\CommandLine\Commands\RestoreAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\SingleForget;
use MuckiRestic\Library\CommandLine\Commands\SingleForgetAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Snapshots;
use MuckiRestic\Library\CommandLine\Commands\SnapshotsAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Stats;
use MuckiRestic\Library\CommandLine\Commands\StatsAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Unlock;
use MuckiRestic\Library\CommandLine\Commands\UnlockAmazonS3;

class CommandBuilderTest extends TestCase
{
    private const BINARY   = '/usr/bin/restic';
    private const REPO     = '/srv/repo';
    private const ENDPOINT = 's3:https://s3.example.com/bucket';

    private function config(): Configuration
    {
        $configuration = BackupClient::create();
        $configuration->setBinaryPath(self::BINARY);
        $configuration->setRepositoryPassword('secret');
        $configuration->setRepositoryPath(self::REPO);
        $configuration->setBackupPath('/srv/data');
        $configuration->setRestoreTarget('/srv/restore');
        $configuration->setSnapshotId('abc12345');
        $configuration->setSnapshotIds(['abc12345', 'def67890']);
        $configuration->setHostName('shop01');
        $configuration->setGroupBy('host');
        $configuration->setTags(['nightly']);
        $configuration->setKeepDaily(7);
        $configuration->setKeepWeekly(5);
        $configuration->setKeepMonthly(12);
        $configuration->setKeepYearly(75);
        $configuration->setKeepLast(3);
        $configuration->setAwsS3Endpoint(self::ENDPOINT);

        return $configuration;
    }

    public function testInit(): void
    {
        $this::assertSame(
            [self::BINARY, 'init', '--repo='.self::REPO, '--json'],
            Init::getCommandLine($this->config())
        );
    }

    public function testInitAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'init', '--repo='.self::ENDPOINT, '--json'],
            InitAmazonS3::getCommandLine($this->config())
        );
    }

    public function testBackup(): void
    {
        $this::assertSame([
            self::BINARY,
            '--repo='.self::REPO,
            'backup',
            '--json',
            '--compression=auto',
            '--host=shop01',
            '--tag=nightly',
            '--',
            '/srv/data',
        ], Backup::getCommandLine($this->config()));
    }

    public function testBackupAmazonS3(): void
    {
        $this::assertSame([
            self::BINARY,
            '--repo='.self::ENDPOINT,
            'backup',
            '--json',
            '--compression=auto',
            '--host=shop01',
            '--tag=nightly',
            '--',
            '/srv/data',
        ], BackupAmazonS3::getCommandLine($this->config()));
    }

    public function testCheck(): void
    {
        $this::assertSame(
            [self::BINARY, 'check', '--read-data'],
            Check::getCommandLine($this->config())
        );
    }

    public function testCheckAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::ENDPOINT, 'check', '--read-data'],
            CheckAmazonS3::getCommandLine($this->config())
        );
    }

    public function testSnapshots(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::REPO, 'snapshots', '--json', '--host=shop01'],
            Snapshots::getCommandLine($this->config())
        );
    }

    public function testSnapshotsAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::ENDPOINT, 'snapshots', '--json', '--host=shop01'],
            SnapshotsAmazonS3::getCommandLine($this->config())
        );
    }

    public function testForget(): void
    {
        $this::assertSame([
            self::BINARY,
            'forget',
            '--repo='.self::REPO,
            '--prune',
            '--json',
            '--host=shop01',
            '--keep-daily=7',
            '--keep-weekly=5',
            '--keep-monthly=12',
            '--keep-yearly=75',
            '--keep-last=3',
            '--group-by=host',
            '--tag=nightly',
            '--',
            'abc12345',
            'def67890',
        ], Forget::getCommandLine($this->config()));
    }

    public function testForgetAmazonS3(): void
    {
        $this::assertSame([
            self::BINARY,
            'forget',
            '--repo='.self::ENDPOINT,
            '--prune',
            '--json',
            '--host=shop01',
            '--keep-daily=7',
            '--keep-weekly=5',
            '--keep-monthly=12',
            '--keep-yearly=75',
            '--keep-last=3',
            '--group-by=host',
            '--tag=nightly',
            '--',
            'abc12345',
            'def67890',
        ], ForgetAmazonS3::getCommandLine($this->config()));
    }

    public function testSingleForget(): void
    {
        $this::assertSame(
            [self::BINARY, 'forget', '--repo='.self::REPO, '--', 'abc12345'],
            SingleForget::getCommandLine($this->config())
        );
    }

    public function testSingleForgetAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'forget', '--repo='.self::ENDPOINT, '--', 'abc12345'],
            SingleForgetAmazonS3::getCommandLine($this->config())
        );
    }

    public function testRestore(): void
    {
        $this::assertSame([
            self::BINARY,
            'restore',
            '--repo='.self::REPO,
            '--target=/srv/restore',
            '--json',
            '--',
            'latest',
        ], Restore::getCommandLine($this->config()));
    }

    public function testRestoreAmazonS3(): void
    {
        $this::assertSame([
            self::BINARY,
            'restore',
            '--repo='.self::ENDPOINT,
            '--target=/srv/restore',
            '--json',
            '--',
            'latest',
        ], RestoreAmazonS3::getCommandLine($this->config()));
    }

    public function testPrune(): void
    {
        $this::assertSame([self::BINARY, 'prune'], Prune::getCommandLine($this->config()));
    }

    public function testPruneAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'prune', '--repo='.self::ENDPOINT],
            PruneAmazonS3::getCommandLine($this->config())
        );
    }

    public function testStats(): void
    {
        $this::assertSame(
            [self::BINARY, 'stats', '--json'],
            Stats::getCommandLine($this->config())
        );
    }

    /**
     * Also covers finding B-5: this class used getRepositoryPath() instead of
     * getAwsS3Endpoint(), unlike every other AmazonS3 command.
     */
    public function testStatsAmazonS3UsesTheEndpoint(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::ENDPOINT, 'stats', '--json'],
            StatsAmazonS3::getCommandLine($this->config())
        );
    }

    public function testUnlock(): void
    {
        $this::assertSame([self::BINARY, 'unlock'], Unlock::getCommandLine($this->config()));
    }

    public function testUnlockAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'unlock', '--repo='.self::ENDPOINT],
            UnlockAmazonS3::getCommandLine($this->config())
        );
    }

    public function testListObjects(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::REPO, 'list'],
            ListObjects::getCommandLine($this->config())
        );
    }

    /**
     * Regression for F-01: shell metacharacters stay inside one argv element
     * and are never interpreted.
     */
    public function testShellMetacharactersStayInOneArgument(): void
    {
        $configuration = $this->config();
        $configuration->setBackupPath('/srv/data; touch /tmp/pwned');

        $arguments = Backup::getCommandLine($configuration);

        $this::assertSame('/srv/data; touch /tmp/pwned', $arguments[array_key_last($arguments)]);
        $this::assertContains('--', $arguments);
        foreach ($arguments as $argument) {
            $this::assertIsString($argument);
        }
    }
}
