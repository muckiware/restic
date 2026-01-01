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

    public function testCheckCreateBackupMissingBinaryPathProperty(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->expectExceptionMessageMatches('/restic: not found/');

        $backupFactory = new BackupFactory();
//        $backupFactory->setBinaryPath(TestData::RESTIC_TEST_PATH_0_15);
        $backupFactory->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $backupFactory->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $backupFactory->setBackupPath(TestData::BACKUP_TEST_PATH);

        $method = new ReflectionMethod(BackupFactory::class, 'createBackup');

        $backupFactoryTest = $method->invoke($backupFactory);
    }
}