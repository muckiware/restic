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
namespace MuckiRestic\Test;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Core\Commands;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Library\Backup as BackupClient;

/**
 * Regression guard for F-01: Client::getProcess() must build the Symfony
 * Process from an argument list, never from a shell command line.
 *
 * new Process(array) quotes every argument as its own shell token when the
 * command line is rendered back out; Process::fromShellCommandline() renders
 * the string verbatim, unescaped. That difference is what this test pins
 * down, so that reverting to fromShellCommandline() (even via
 * implode(' ', $command)) fails here even though every CommandBuilderTest
 * case, built on space-free fixture paths, would stay green.
 */
class ClientTest extends TestCase
{
    public function testGetProcessBuildsAnArgumentListProcessNotAShellString(): void
    {
        $client = BackupClient::create();

        $maliciousArgument = '/srv/data; touch /tmp/pwned';
        $process = $client->getProcess([
            '/usr/bin/restic',
            '--repo=/srv/repo',
            'backup',
            '--',
            $maliciousArgument,
        ]);

        $this::assertStringContainsString(
            "'".$maliciousArgument."'",
            $process->getCommandLine(),
            'getProcess() must quote/escape each argument, which only happens when Process is built from an array; '
            .'Process::fromShellCommandline() would render the string unescaped instead.'
        );
    }

    public function testProcessTimeoutDefaultsToOneHour(): void
    {
        $this::assertSame(3600.0, BackupClient::create()->getProcessTimeout());
        $this::assertSame(3600.0, BackupClient::create()->getProcess(['/bin/true'])->getTimeout());
    }

    public function testProcessIdleTimeoutIsOffByDefault(): void
    {
        $this::assertNull(BackupClient::create()->getProcessIdleTimeout());
        $this::assertNull(BackupClient::create()->getProcess(['/bin/true'])->getIdleTimeout());
    }

    public function testProcessTimeoutIsConfigurable(): void
    {
        $client = BackupClient::create();
        $client->setProcessTimeout(7200);

        $this::assertSame(7200.0, $client->getProcessTimeout());
        $this::assertSame(7200.0, $client->getProcess(['/bin/true'])->getTimeout());
    }

    public function testProcessIdleTimeoutIsConfigurable(): void
    {
        $client = BackupClient::create();
        $client->setProcessIdleTimeout(300);

        $this::assertSame(300.0, $client->getProcessIdleTimeout());
        $this::assertSame(300.0, $client->getProcess(['/bin/true'])->getIdleTimeout());
    }

    public function testNullProcessTimeoutRemovesTheLimit(): void
    {
        $client = BackupClient::create();
        $client->setProcessTimeout(null);

        $this::assertNull($client->getProcessTimeout());
        $this::assertNull($client->getProcess(['/bin/true'])->getTimeout());
    }

    /**
     * Symfony reads a timeout of 0 as "no limit". An unfilled integer field in a
     * consuming admin UI yields exactly 0, which would silently disable the limit
     * on a backup process. Null is the explicit way to ask for no limit.
     */
    public function testZeroProcessTimeoutIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        BackupClient::create()->setProcessTimeout(0);
    }

    public function testNegativeProcessTimeoutIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        BackupClient::create()->setProcessTimeout(-1);
    }

    public function testZeroProcessIdleTimeoutIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        BackupClient::create()->setProcessIdleTimeout(0);
    }

    public function testNegativeProcessIdleTimeoutIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        BackupClient::create()->setProcessIdleTimeout(-0.5);
    }

    /**
     * The facades hand their state to the factory and on to the implementation class
     * through ReflectionObject::getProperties(). A configuration value that is not a
     * plain protected property silently fails to make that hop, so pin the whole path
     * rather than just the setter.
     */
    public function testTimeoutsSurviveThePropagationToTheBuiltProcess(): void
    {
        $client = BackupClient::create();
        $client->setBinaryPath('/usr/bin/restic');
        $client->setRepositoryPassword('secret');
        $client->setRepositoryPath('/srv/repo');
        $client->setBackupPath('/srv/data');
        $client->setProcessTimeout(7200);
        $client->setProcessIdleTimeout(600);

        $process = $client->createProcess(Commands::BACKUP);

        $this::assertSame(7200.0, $process->getTimeout());
        $this::assertSame(600.0, $process->getIdleTimeout());
    }
}
