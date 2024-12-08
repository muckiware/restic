<?php

namespace MuckiRestic\Test\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Depends;

use MuckiRestic\Test\TestData;
use MuckiRestic\Test\TestHelper;
use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Manage;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Service\Helper;

class IntegrationTest extends TestCase
{
    protected Backup $backupClient;
    protected Manage $manageClient;

    public function createSetup(): void
    {
        Helper::deleteDirectory(TestData::REPOSITORY_TEST_PATH);
        Helper::deleteDirectory(TestData::BACKUP_TEST_PATH);

        $this->backupClient = Backup::create();
        $this->backupClient->setBinaryPath(TestData::RESTIC_TEST_PATH);
        $this->backupClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->backupClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $this->backupClient->setBackupPath(TestData::BACKUP_TEST_PATH);

        $this->manageClient = Manage::create();
        $this->manageClient->setBinaryPath(TestData::RESTIC_TEST_PATH);
        $this->manageClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->manageClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $this->manageClient->setBackupPath(TestData::BACKUP_TEST_PATH);
    }
    public function testIntegration(): void
    {
        $this->createSetup();
        $resultInit = $this->backupClient->createRepository(true);

        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultInit->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');
        $this->assertIsFloat($resultInit->getDuration(), 'Duration should be a float');

        $this->backupRepository();
        $this->backupNextRepository();
        $this->checkRepository();
        $this->getSnapshots();
    }
    public function backupRepository(): void
    {
        TestHelper::createTextFiles(TestData::BACKUP_TEST_PATH, TestData::BACKUP_TEST_FILES);
        $resultBackup = $this->backupClient->createBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultBackup, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultBackup->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($resultBackup->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultBackup->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultBackup->getDuration(), 'Duration should be a float');
    }

    public function backupNextRepository(): void
    {
        TestHelper::createTextFiles(TestData::BACKUP_TEST_PATH, TestData::NEXT_BACKUP_TEST_FILES);
        $resultBackup = $this->backupClient->createBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultBackup, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultBackup->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($resultBackup->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultBackup->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultBackup->getDuration(), 'Duration should be a float');
    }

    #[Depends('testBackupRepository')]
    public function checkRepository(): void
    {
        $resultCheck = $this->backupClient->checkBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');
    }

    #[Depends('testBackupRepository')]
    public function getSnapshots(): void
    {
        $resultCheck = $this->manageClient->getSnapshots();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');
        $this->assertIsArray($resultCheck->getResticResponse(), 'Restic response should be an array');
        $this->assertCount(2, $resultCheck->getResticResponse(), 'Restic response should have 2 snapshots');
    }

    public function testGetResticVersion(): void
    {
        $this->createSetup();
        $resultVersion = $this->backupClient->getResticVersion();

        $this->assertInstanceOf(ResultEntity::class, $resultVersion, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultVersion->getOutput(), 'Output should be a string');
    }
}