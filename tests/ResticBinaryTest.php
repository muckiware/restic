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
 * Until now a missing binary and an unreadable version were indistinguishable: both
 * surfaced as "Not supported restic version", and a missing binary only failed once the
 * process had already been started, as a ProcessFailedException with exit code 127.
 * Neither named the path that was actually tried.
 */
class ResticBinaryTest extends TestCase
{
    /** @var list<string> */
    private array $createdPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->createdPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->createdPaths = [];
    }

    private function configuredClient(string $binaryPath): BackupClient
    {
        $client = BackupClient::create();
        $client->setBinaryPath($binaryPath);
        $client->setRepositoryPassword('secret');
        $client->setRepositoryPath('/srv/repo');
        $client->setBackupPath('/srv/data');

        return $client;
    }

    public function testCreateProcessRejectsABinaryPathThatDoesNotExist(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('#/does/not/exist/restic#');

        $this->configuredClient('/does/not/exist/restic')->createProcess(Commands::BACKUP);
    }

    public function testCreateProcessRejectsABareNameThatIsNotOnThePath(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/definitely-not-a-restic-binary/');

        $this->configuredClient('definitely-not-a-restic-binary')->createProcess(Commands::BACKUP);
    }

    public function testCreateProcessRejectsAFileThatIsNotExecutable(): void
    {
        $path = sys_get_temp_dir().'/muckirestic_notexec_'.bin2hex(random_bytes(6));
        file_put_contents($path, "#!/bin/sh\n");
        chmod($path, 0644);
        $this->createdPaths[] = $path;

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/'.preg_quote(basename($path), '/').'/');

        $this->configuredClient($path)->createProcess(Commands::BACKUP);
    }

    public function testCreateProcessAcceptsAnExecutableAbsolutePath(): void
    {
        $process = $this->configuredClient('/bin/true')->createProcess(Commands::BACKUP);

        $this::assertStringContainsString('/bin/true', $process->getCommandLine());
    }

    /**
     * The configuration default is the bare name 'restic', so a value without a
     * separator has to be resolved through PATH rather than against the working
     * directory. 'sh' stands in for a binary that is genuinely on the PATH.
     */
    public function testCreateProcessAcceptsABareNameFoundOnThePath(): void
    {
        $process = $this->configuredClient('sh')->createProcess(Commands::BACKUP);

        $this::assertStringContainsString('sh', $process->getCommandLine());
    }

    public function testVersionDetectionRejectsABinaryPathThatDoesNotExist(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('#/does/not/exist/restic#');

        $this->configuredClient('/does/not/exist/restic')->getResticVersion();
    }

    /**
     * A binary that exists but is not restic is a different problem from a binary that
     * is missing, and the message has to say so — and name the path, which the old
     * RuntimeException did not.
     */
    public function testVersionDetectionRejectsOutputThatCarriesNoVersion(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('#/bin/true#');

        $this->configuredClient('/bin/true')->getResticVersion();
    }

    public function testCreateVersionNamesTheBinaryAndTheOutputItCouldNotRead(): void
    {
        $client = $this->configuredClient('/bin/true');

        try {
            $client->createVersion('this is not a restic version banner');
            $this::fail('Expected an InvalidConfigurationException for unreadable version output');
        } catch (InvalidConfigurationException $exception) {
            $this::assertStringContainsString('/bin/true', $exception->getMessage());
            $this::assertStringContainsString('this is not a restic version banner', $exception->getMessage());
        }
    }
}
