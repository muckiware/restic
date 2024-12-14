<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Test\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Depends;

use MuckiRestic\Test\TestData;
use MuckiRestic\Test\TestHelper;
use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Manage;
use MuckiRestic\Library\Restore;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Service\Helper;

class IntegrationTest extends TestCase
{
    protected Backup $backupClient;
    protected Manage $manageClient;
    protected Restore $restoreClient;

    public function createSetup(): void
    {
        Helper::deleteDirectory(TestData::REPOSITORY_TEST_PATH);
        Helper::deleteDirectory(TestData::BACKUP_TEST_PATH);
        Helper::deleteDirectory(TestData::RESTORE_TEST_PATH);

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

        $this->restoreClient = Restore::create();
        $this->restoreClient->setBinaryPath(TestData::RESTIC_TEST_PATH);
        $this->restoreClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->restoreClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $this->restoreClient->setRestoreTarget(TestData::RESTORE_TEST_PATH);
    }
    public function testIntegration(): void
    {
        $this->createSetup();
        $resultInit = $this->backupClient->createRepository(true);

        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');
        $this->assertIsFloat($resultInit->getDuration(), 'Duration should be a float');
        $this->assertSame('initialized', $resultInit->getResticResponse()->message_type, 'Repository should be initialized');
        $this->assertIsString($resultInit->getResticResponse()->id, 'Repository id should be a string');
        $this->assertSame(
            $resultInit->getResticResponse()->repository,
            TestData::REPOSITORY_TEST_PATH,
            'Repository path should be '.TestData::REPOSITORY_TEST_PATH
        );

        $this->backupRepository();
        $this->backupNextRepository();
        $this->checkRepository();
        $this->getSnapshots();
        $this->executeForget();
        $this->createRestore();
    }
    public function backupRepository(): void
    {
        TestHelper::createTextFiles(TestData::BACKUP_TEST_PATH, TestData::BACKUP_TEST_FILES);
        $resultBackup = $this->backupClient->createBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultBackup, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultBackup->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultBackup->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultBackup->getDuration(), 'Duration should be a float');

        foreach ($resultBackup->getResticResponse() as $response) {

            if($response->message_type === 'summary') {
                $this->assertIsString($response->snapshot_id, 'Snapshot id should be a string');
            }
        }
    }

    public function backupNextRepository(): void
    {
        TestHelper::createTextFiles(TestData::BACKUP_TEST_PATH, TestData::NEXT_BACKUP_TEST_FILES);
        $resultBackup = $this->backupClient->createBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultBackup, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultBackup->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultBackup->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultBackup->getDuration(), 'Duration should be a float');

        foreach ($resultBackup->getResticResponse() as $response) {

            if($response->message_type === 'summary') {
                $this->assertIsString($response->snapshot_id, 'Snapshot id should be a string');
            }
        }
    }

    #[Depends('testBackupRepository')]
    public function checkRepository(): void
    {
        $resultCheck = $this->backupClient->checkBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');

        $processed = $resultCheck->getProcessed();
        $lastProcess = end($processed);
        $this->assertSame('no errors were found', $lastProcess, 'Message should be "no errors were found"');
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

    public function executeForget(): void
    {
        $resultCheck = $this->manageClient->executeForget();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');
    }

    public function createRestore(): void
    {
        $resultCheck = $this->restoreClient->createRestore();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');
        $this->assertIsArray($resultCheck->getResticResponse(), 'Restic response should be an array');

        $fileCounter = 1;
        foreach (TestData::BACKUP_TEST_FILES as $file) {

            $filePath = TestData::RESTORE_TEST_PATH.str_replace('.','',TestData::BACKUP_TEST_PATH).DIRECTORY_SEPARATOR.'file'.$fileCounter.'.txt';
            $this->assertFileExists($filePath, 'File '.$filePath.'should exist');
        }

        foreach (TestData::NEXT_BACKUP_TEST_FILES as $file) {

            $filePath = TestData::RESTORE_TEST_PATH.str_replace('.','',TestData::BACKUP_TEST_PATH).DIRECTORY_SEPARATOR.'file'.$fileCounter.'.txt';
            $this->assertFileExists($filePath, 'File '.$filePath.'should exist');
        }
    }

    public function testGetResticVersion(): void
    {
        $this->createSetup();
        $resultVersion = $this->backupClient->getResticVersion();

        $this->assertInstanceOf(ResultEntity::class, $resultVersion, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultVersion->getOutput(), 'Output should be a string');
    }
}