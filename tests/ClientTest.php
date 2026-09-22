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
}
