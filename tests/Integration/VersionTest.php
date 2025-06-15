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

class VersionTest extends TestCase
{
    protected Backup $backupClient;
    protected Manage $manageClient;
    protected Restore $restoreClient;

    public function createSetup($resticBinaryPath): void
    {
        $this->backupClient = Backup::create();
        $this->backupClient->setBinaryPath($resticBinaryPath);
        $this->backupClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->backupClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
    }

    public function testGetResticVersion018(): void
    {
        $this->createSetup(TestData::RESTIC_TEST_PATH_0_18);
        $resultVersion = $this->backupClient->getResticVersion();

        $this->assertInstanceOf(ResultEntity::class, $resultVersion, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultVersion->getOutput(), 'Output should be a string');
        $this->assertSame('0.18.0', $resultVersion->getResticResponse()->getVersion(), 'Expected version should be 0.18.0');
    }

    public function testGetResticVersion017(): void
    {
        $this->createSetup(TestData::RESTIC_TEST_PATH_0_17);
        $resultVersion = $this->backupClient->getResticVersion();

        $this->assertInstanceOf(ResultEntity::class, $resultVersion, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultVersion->getOutput(), 'Output should be a string');
        $this->assertSame('0.17.3', $resultVersion->getResticResponse()->getVersion(), 'Expected version should be 0.17.3');
    }

    public function testGetResticVersion016(): void
    {
        $this->createSetup(TestData::RESTIC_TEST_PATH_0_16);
        $resultVersion = $this->backupClient->getResticVersion();

        $this->assertInstanceOf(ResultEntity::class, $resultVersion, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultVersion->getOutput(), 'Output should be a string');
        $this->assertSame('0.16.5', $resultVersion->getResticResponse()->getVersion(), 'Expected version should be 0.16.5');
    }

    public function testGetResticVersion015(): void
    {
        $this->createSetup(TestData::RESTIC_TEST_PATH_0_15);
        $resultVersion = $this->backupClient->getResticVersion();

        $this->assertInstanceOf(ResultEntity::class, $resultVersion, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultVersion->getOutput(), 'Output should be a string');
        $this->assertSame('0.15.2', $resultVersion->getResticResponse()->getVersion(), 'Expected version should be 0.15.2');
    }

    public function testGetResticVersion014(): void
    {
        $this->createSetup(TestData::RESTIC_TEST_PATH_0_14);
        $resultVersion = $this->backupClient->getResticVersion();

        $this->assertInstanceOf(ResultEntity::class, $resultVersion, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultVersion->getOutput(), 'Output should be a string');
        $this->assertSame('0.14.0', $resultVersion->getResticResponse()->getVersion(), 'Expected version should be 0.14.0');
    }
}
