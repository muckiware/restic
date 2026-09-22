<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Test\Library;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Process\Exception\ProcessFailedException;

use MuckiRestic\Library\Backup;
use MuckiRestic\Library\BackupFactory;
use MuckiRestic\Test\TestData;
use MuckiRestic\Test\TestHelper;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Exception\InvalidConfigurationException;

class BackupTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testCreateFactoryInstanceReturnsBackupFactory(): void
    {
        $method = new ReflectionMethod(Backup::class, 'createFactoryInstance');
        $backupFactory = $method->invoke(new Backup());

        $this->assertInstanceOf(BackupFactory::class, $backupFactory);
    }

    public function testCheckCreateBackup(): void
    {
        $backupFactory = new BackupFactory();
        $backupFactory->setBinaryPath(TestData::RESTIC_TEST_PATH_0_15);
        $backupFactory->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $backupFactory->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $backupFactory->setBackupPath(TestData::BACKUP_TEST_PATH);

        $method = new ReflectionMethod(BackupFactory::class, 'createBackup');

        $backupFactoryTest = $method->invoke($backupFactory);
        $this->assertInstanceOf(ResultEntity::class, $backupFactoryTest);
    }

    public function testCheckCreateBackupMissingBackupPathProperty(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Missing required parameter backupPath');

        $backupFactory = new BackupFactory();
        $backupFactory->setBinaryPath(TestData::RESTIC_TEST_PATH_0_15);
        $backupFactory->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $backupFactory->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
//        $backupFactory->setBackupPath(TestData::BACKUP_TEST_PATH);

        $method = new ReflectionMethod(BackupFactory::class, 'createBackup');

        $backupFactoryTest = $method->invoke($backupFactory);
    }

    public function testCheckCreateBackupMissingRepositoryPathProperty(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Missing required parameter repositoryPath');

        $backupFactory = new BackupFactory();
        $backupFactory->setBinaryPath(TestData::RESTIC_TEST_PATH_0_15);
        $backupFactory->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
//        $backupFactory->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $backupFactory->setBackupPath(TestData::BACKUP_TEST_PATH);

        $method = new ReflectionMethod(BackupFactory::class, 'createBackup');

        $backupFactoryTest = $method->invoke($backupFactory);
    }

    public function testCheckCreateBackupMissingRepositoryPasswordProperty(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Missing required parameter repositoryPassword');

        $backupFactory = new BackupFactory();
        $backupFactory->setBinaryPath(TestData::RESTIC_TEST_PATH_0_15);
//        $backupFactory->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $backupFactory->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $backupFactory->setBackupPath(TestData::BACKUP_TEST_PATH);

        $method = new ReflectionMethod(BackupFactory::class, 'createBackup');

        $backupFactoryTest = $method->invoke($backupFactory);
    }

    /**
     * With no binary path set, the configuration default 'restic' is used and the
     * process cannot be started.
     *
     * This reads the Process rather than the exception text on purpose. Until 1.5.0
     * every command went through /bin/sh -c, so stderr always carried
     * "restic: not found" and the test could match on it. Without a shell that message
     * only appears when PHP's proc_open rejects the argument-list form and Symfony
     * falls back to a shell command line — which happens on PHP 8.4 but not on 8.2,
     * so the old assertion passed locally and failed in CI. Exit code 127 and the
     * rendered command line are identical on both paths.
     *
     * @throws \ReflectionException
     */
    public function testCheckCreateBackupMissingBinaryPathProperty(): void
    {
        $backupFactory = new BackupFactory();
//        $backupFactory->setBinaryPath(TestData::RESTIC_TEST_PATH_0_15);
        $backupFactory->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $backupFactory->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $backupFactory->setBackupPath(TestData::BACKUP_TEST_PATH);

        $method = new ReflectionMethod(BackupFactory::class, 'createBackup');

        try {
            $method->invoke($backupFactory);
            $this::fail('Expected a ProcessFailedException because no restic binary is available');
        } catch (ProcessFailedException $exception) {
            $this::assertSame(
                127,
                $exception->getProcess()->getExitCode(),
                'A missing binary must fail the process with exit code 127 (command not found)'
            );
            $this::assertStringContainsString(
                'restic',
                $exception->getProcess()->getCommandLine(),
                'The default binary name from the configuration must appear in the command line'
            );
        }
    }
}