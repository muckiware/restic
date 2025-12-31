<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2025 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Test\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Depends;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

use MuckiRestic\Test\TestData;
use MuckiRestic\Test\TestHelper;
use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Manage;
use MuckiRestic\Library\Restore;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Service\Helper;
use MuckiRestic\Entity\Result\ResticResponse\Snapshot;
use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Environment\EnvironmentHelper;

class IntegrationAwsS3Test extends TestCase
{
    protected Backup $backupClient;
    protected Manage $manageClient;
    protected Restore $restoreClient;

    public function createSetup($resticBinaryPath): void
    {
        Helper::deleteDirectory(TestData::BACKUP_TEST_PATH);
        Helper::deleteDirectory(TestData::RESTORE_TEST_PATH);

        $this->backupClient = Backup::create();
        $this->backupClient->setBinaryPath($resticBinaryPath);
        $this->backupClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->backupClient->setBackupPath(TestData::BACKUP_TEST_PATH);
        $this->backupClient->setCompress(true);
        $this->backupClient->setSkipPrepareBackup(false);
        $this->backupClient->setTag(TestData::BACKUP_TEST_TAG);
        $this->backupClient->setHostName(TestData::BACKUP_TEST_HOSTNAME);
        $this->backupClient->setAwsAccessKeyId(EnvironmentHelper::getVariable('AWS_ACCESS_KEY_ID'));
        $this->backupClient->setAwsSecretAccessKey(EnvironmentHelper::getVariable('AWS_SECRET_ACCESS_KEY'));
        $this->backupClient->setAwsS3Endpoint(EnvironmentHelper::getVariable('AWS_ENDPOINT_URL'));
        $this->backupClient->setAwsS3Region(EnvironmentHelper::getVariable('AWS_REGION'));
        $this->backupClient->setAwsS3BucketName(EnvironmentHelper::getVariable('AWS_S3_BUCKET_NAME'));

        $this->manageClient = Manage::create();
        $this->manageClient->setBinaryPath($resticBinaryPath);
        $this->manageClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->manageClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $this->manageClient->setBackupPath(TestData::BACKUP_TEST_PATH);
        $this->manageClient->setKeepDaily(7);
        $this->manageClient->setKeepWeekly(5);
        $this->manageClient->setKeepMonthly(12);
        $this->manageClient->setKeepYearly(75);
        $this->manageClient->setAwsAccessKeyId(EnvironmentHelper::getVariable('AWS_ACCESS_KEY_ID'));
        $this->manageClient->setAwsSecretAccessKey(EnvironmentHelper::getVariable('AWS_SECRET_ACCESS_KEY'));
        $this->manageClient->setAwsS3Endpoint(EnvironmentHelper::getVariable('AWS_ENDPOINT_URL'));

        $this->restoreClient = Restore::create();
        $this->restoreClient->setBinaryPath($resticBinaryPath);
        $this->restoreClient->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $this->restoreClient->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $this->restoreClient->setRestoreTarget(TestData::RESTORE_TEST_PATH);
//        $this->restoreClient->setRestoreItem();
    }

    public function testIntegration015(): void
    {
        $this->createSetup(TestData::RESTIC_TEST_PATH_0_15);

        $resultInit = $this->backupClient->createRepository(true, RepositoryLocationTypes::AWSS3);

        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');
        $this->assertIsFloat($resultInit->getDuration(), 'Duration should be a float');
        $this->assertSame('initialized', $resultInit->getResticResponse()->message_type, 'Repository should be initialized');
        $this->assertIsString($resultInit->getResticResponse()->id, 'Repository id should be a string');
        $this->assertSame(
            $resultInit->getResticResponse()->repository,
            EnvironmentHelper::getVariable('AWS_ENDPOINT_URL'),
            'Repository path should be '.EnvironmentHelper::getVariable('AWS_ENDPOINT_URL')
        );

        $this->backupRepository();
//        $this->backupNextRepository();
//        $this->checkRepository();
//        $this->getSnapshots();
//        $this->removeSnapshotById();
//        $this->removeSnapshots();
//        $this->createRestore();
//        $this->removeSnapshot();
    }

//    public function testIntegration016(): void
//    {
//        $this->createSetup(TestData::RESTIC_TEST_PATH_0_16);
//
//        $resultInit = $this->backupClient->createRepository(true);
//
//        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
//        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');
//        $this->assertIsFloat($resultInit->getDuration(), 'Duration should be a float');
//        $this->assertSame('initialized', $resultInit->getResticResponse()->message_type, 'Repository should be initialized');
//        $this->assertIsString($resultInit->getResticResponse()->id, 'Repository id should be a string');
//        $this->assertSame(
//            $resultInit->getResticResponse()->repository,
//            TestData::REPOSITORY_TEST_PATH,
//            'Repository path should be '.TestData::REPOSITORY_TEST_PATH
//        );

//        $this->backupRepository();
//        $this->backupNextRepository();
//        $this->checkRepository();
//        $this->getSnapshots();
//        $this->removeSnapshotById();
//        $this->removeSnapshots();
//        $this->createRestore();
//        $this->removeSnapshot();
//    }

//    public function testIntegration017(): void
//    {
//        $this->createSetup(TestData::RESTIC_TEST_PATH_0_17);
//
//        $resultInit = $this->backupClient->createRepository(true);
//
//        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
//        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');
//        $this->assertIsFloat($resultInit->getDuration(), 'Duration should be a float');

//        $this->backupRepository();
//        $this->backupNextRepository();
//        $this->checkRepository();
//        $this->getSnapshots();
//        $this->removeSnapshotById();
//        $this->removeSnapshots();
//        $this->createRestore();
//        $this->removeSnapshot();
//    }

//    public function testIntegration018(): void
//    {
//        $this->createSetup(TestData::RESTIC_TEST_PATH_0_18);
//
//        $resultInit = $this->backupClient->createRepository(true);
//
//        $this->assertInstanceOf(ResultEntity::class, $resultInit, 'Result should be an instance of ResultEntity');
//        $this->assertIsString($resultInit->getCommandLine(), 'Command line should be a string');
//        $this->assertIsFloat($resultInit->getDuration(), 'Duration should be a float');
//
//        $this->backupRepository();
//        $this->backupNextRepository();
//        $this->checkRepository();
//        $this->getSnapshots();
//        $this->removeSnapshotById();
//        $this->removeSnapshots();
//        $this->createRestore();
//        $this->removeSnapshot();
//    }
    public function backupRepository(): void
    {
        TestHelper::createTextFiles(TestData::BACKUP_TEST_PATH, TestData::BACKUP_TEST_FILES);
        $resultBackup = $this->backupClient->createBackup(RepositoryLocationTypes::AWSS3);

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

        $snapshotCollection = $resultCheck->getResticResponse();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertInstanceOf(ArrayCollection::class, $snapshotCollection, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');
        $this->assertCount(2, $snapshotCollection, 'Restic response should have 2 snapshots');

        /** @var Snapshot $snapshot */
        foreach ($snapshotCollection as $snapshot) {

            $hostname = $snapshot->getHostname();
            $this->assertIsString($hostname, 'Hostname should be a string');
            $this->assertSame(TestData::BACKUP_TEST_HOSTNAME, $hostname, 'Hostname should be '.TestData::BACKUP_TEST_HOSTNAME);
        }
    }

    public function removeSnapshotById(): void
    {
        $snapshotCollectionBefore = $this->manageClient->getSnapshots();
        $this->assertCount(2, $snapshotCollectionBefore->getResticResponse(), 'Restic response should have 2 snapshots before remove');

        /** @var Snapshot $firstSnapshot */
        $firstSnapshot = $snapshotCollectionBefore->getResticResponse()->first();

        $snapshotId = $firstSnapshot->getId();
        $this->manageClient->setSnapshotId($snapshotId);
        $resultCheck = $this->manageClient->removeSnapshotById();

        $this->assertInstanceOf(ResultEntity::class, $resultCheck, 'Result should be an instance of ResultEntity');
        $this->assertIsString($resultCheck->getCommandLine(), 'Command line should be a string');
        $this->assertIsString($resultCheck->getOutput(), 'Output should be a string');
        $this->assertIsFloat($resultCheck->getDuration(), 'Duration should be a float');

        $snapshotCollectionAfter = $this->manageClient->getSnapshots();
        $this->assertCount(1, $snapshotCollectionAfter->getResticResponse(), 'Restic response should have 1 snapshots after remove');
    }

    public function removeSnapshots(): void
    {
        $snapshotCollectionBefore = $this->manageClient->getSnapshots();

        $resultCheck = $this->manageClient->removeSnapshots();

        $snapshotCollectionAfter = $this->manageClient->getSnapshots();

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

    public function removeSnapshot(): void
    {
        $this->backupClient->createBackup();
        $snapshotCollection = $this->manageClient->getSnapshots()->getResticResponse();
        $this->assertCount(2, $snapshotCollection, 'Restic response should have 2 snapshots');

        /** @var Snapshot $lastSnapshot */
        $lastSnapshot = $this->manageClient->getSnapshots()->getResticResponse()->last();
        $firstSnapshot = $this->manageClient->getSnapshots()->getResticResponse()->first();
        $this->assertInstanceOf(Snapshot::class, $lastSnapshot, 'last item of snapshot collection should be an instance of Snapshot');

        $lastSnapshotShortId = $lastSnapshot->getShortId();
        $firstSnapshotShortId = $firstSnapshot->getShortId();
        $this->manageClient->setSnapshotId($lastSnapshotShortId);
        $this->manageClient->removeSnapshotById();

        $snapshotsAfterRemove = $this->manageClient->getSnapshots();

        /** @var ArrayCollection $snapshotCollectionAfterRemove */
        $snapshotCollectionAfterRemove = $snapshotsAfterRemove->getResticResponse();
        $this->assertCount(1, $snapshotCollectionAfterRemove, 'Restic response should have 1 snapshots after remove');

        $criteria = new Criteria();
        $criteria->where(new Comparison('short_id', '=', $firstSnapshotShortId));
        $criteria->orwhere(new Comparison('short_id', '=', $lastSnapshotShortId));
        $matchingCollection = $snapshotCollectionAfterRemove->matching($criteria);

        $this->assertCount(1, $matchingCollection, 'Restic response should have 1 snapshots after remove');
    }
}
