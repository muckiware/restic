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
     * With no binary path set, the configuration default 'restic' is used. The library
     * now resolves that name through PATH before starting anything and reports a
     * missing binary as the configuration error it is, naming the path.
     *
     * Two earlier shapes of this test were environment-dependent: matching stderr for
     * "restic: not found" only worked while a shell was involved (PHP 8.4 falls back to
     * one, PHP 8.2 does not, which is why it passed locally and failed in CI), and
     * asserting exit code 127 tested the operating system rather than this library.
     *
     * @throws \ReflectionException
     */
    public function testCheckCreateBackupMissingBinaryPathProperty(): void
    {
        if (self::resticIsOnThePath()) {
            $this::markTestSkipped(
                'A restic binary is present in PATH, so the default binary name resolves '
                .'and the premise of this test does not hold.'
            );
        }

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/restic/');

        $backupFactory = new BackupFactory();
//        $backupFactory->setBinaryPath(TestData::RESTIC_TEST_PATH_0_15);
        $backupFactory->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $backupFactory->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $backupFactory->setBackupPath(TestData::BACKUP_TEST_PATH);

        $method = new ReflectionMethod(BackupFactory::class, 'createBackup');

        $method->invoke($backupFactory);
    }

    private static function resticIsOnThePath(): bool
    {
        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $directory) {
            if ($directory !== '' && is_executable($directory.DIRECTORY_SEPARATOR.'restic')) {
                return true;
            }
        }

        return false;
    }
}