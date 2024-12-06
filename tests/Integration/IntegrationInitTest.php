<?php

namespace Integration;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Backup;
use MuckiRestic\Entity\Result\ResultEntity;

class IntegrationInitTest extends TestCase
{
    public function testInitRepository(): void
    {
        $backupClient = Backup::create();
        $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');
        $backupClient->setRepositoryPassword('1234');
        $backupClient->setRepositoryPath('./var/testRep');

        $result = $backupClient->createRepository(true);

        $this->assertInstanceOf(ResultEntity::class, $result, 'Result should be an instance of ResultEntity');
        $this->assertIsString($result->getSnapshots()->getNew(), 'New snapshot should be a string');
        $this->assertIsString($result->getCommandLine(), 'Command line should be a string');
    }
}