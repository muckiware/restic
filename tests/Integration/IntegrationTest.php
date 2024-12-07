<?php

namespace MuckiRestic\Test\Integration;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Test\TestData;
use MuckiRestic\Library\Backup;
use MuckiRestic\Entity\Result\ResultEntity;

class IntegrationTest extends TestCase
{
    protected Backup $backupClient;
    protected function setUp(): void
    {
        $this->backupClient = Backup::create();
        $this->backupClient->setBinaryPath(TestData::RESTIC_TEST_PATH);
        $this->backupClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->backupClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $this->backupClient->setBackupPath(TestData::BACKUP_TEST_PATH);
    }
    public function testInitRepository(): void
    {
        $resultInit = $this->backupClient->createRepository(true);

        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultInit->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');
        $this->assertIsFloat($resultInit->getDuration(), 'Duration should be a float');
    }

    public function testBackupRepository(): void
    {
        $resultBackup = $this->backupClient->createBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultBackup, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultBackup->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($resultBackup->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultBackup->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultBackup->getDuration(), 'Duration should be a float');
    }

    public function testCheckRepository(): void
    {
        $resultCheck = $this->backupClient->checkBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');
    }
}