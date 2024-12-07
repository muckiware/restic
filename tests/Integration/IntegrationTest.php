<?php

namespace MuckiRestic\Test\Integration;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Test\TestData;
use MuckiRestic\Library\Backup;
use MuckiRestic\Entity\Result\ResultEntity;

class IntegrationTest extends TestCase
{
    public function testInitRepository(): void
    {
        $backupClient = Backup::create();
        $backupClient->setBinaryPath(TestData::RESTIC_TEST_PATH);
        $backupClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $backupClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);

        $resultInit = $backupClient->createRepository(true);

        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultInit->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');

        $backupClient->setBackupPath(TestData::BACKUP_TEST_PATH);
        $resultBackup = $backupClient->createBackup();

        $this->assertInstanceOf(ResultEntity::class, $resultBackup, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultBackup->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($resultBackup->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultBackup->getOutput(), 'Output should be a string');
    }
}