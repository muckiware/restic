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
namespace MuckiRestic\Library\Backup;

use MuckiRestic\Library\Configuration;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Aws\S3\S3Client;

use MuckiRestic\ResultParser\InitResultParser;
use MuckiRestic\ResultParser\BackupResultParser;
use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;
use MuckiRestic\Service\Json;
use MuckiRestic\Library\BackupInterface;

class AmazonS3 extends Configuration implements BackupInterface
{
    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createRepository(bool $overwrite=false): ResultEntity
    {
        $this->setRepositoryPath('/');
        if(!$this->checkInputParametersByCommand(Commands::INIT_AMAZON_S3)) {
            throw new InvalidConfigurationException('Invalid configuration');
        }

        if($overwrite) {
           $this->removeOldRepository();
        }

        $process = $this->createProcess(Commands::INIT_AMAZON_S3);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $initResult = new ResultEntity();
        $initResult->setCommandLine($process->getCommandLine());
        $initResult->setStatus($process->getStatus());
        $initResult->setStartTime($process->getStartTime());
        $initResult->setEndTime($process->getLastOutputTime());
        $initResult->setDuration();
        $initResult->setOutput($process->getOutput());
        $initResult->setResticResponse(Json::decode($process->getOutput()));

        return $initResult;
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createBackup(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::BACKUP)) {

            if(!$this->skipPrepareBackup) {
                $this->prepareBackup();
            }
            $process = $this->createProcess(Commands::BACKUP);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            if($this->isJsonOutput()) {
                $backupOutput = Json::decode(BackupResultParser::fixJsonOutput($process->getOutput()));
            } else {
                $backupOutput = $process->getOutput();
            }

            $backupResult = new ResultEntity();
            $backupResult->setCommandLine($process->getCommandLine());
            $backupResult->setStatus($process->getStatus());
            $backupResult->setStartTime($process->getStartTime());
            $backupResult->setEndTime($process->getLastOutputTime());
            $backupResult->setDuration();
            $backupResult->setOutput($process->getOutput());
            $backupResult->setResticResponse($backupOutput);

            return $backupResult;

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function prepareBackup(): void
    {
        $this->runUnlockCommand();
        $this->runPruneCommand();
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function checkBackup(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::CHECK)) {

            $process = $this->createProcess(Commands::CHECK);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $checkResult = CheckResultParser::textParserResult($process->getOutput());
            $checkResult->setCommandLine($process->getCommandLine());
            $checkResult->setStatus($process->getStatus());
            $checkResult->setStartTime($process->getStartTime());
            $checkResult->setEndTime($process->getLastOutputTime());
            $checkResult->setDuration();
            $checkResult->setOutput($process->getOutput());

            return $checkResult;

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function runUnlockCommand(): void
    {
        $process = $this->createProcess(Commands::UNLOCK);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function runPruneCommand(): void
    {
        $process = $this->createProcess(Commands::PRUNE);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function removeOldRepository(): void
    {
        $bucketObjectsContent = null;
        $s3client = $this->getS3Client();

        $bucketObjects = $s3client->listObjectsV2([
            'Bucket' => $this->getAwsS3BucketName(),
        ]);

        foreach ($bucketObjects['Contents'] as $content) {
            $bucketObjectsContent[] = [
                'Key' => $content['Key'],
            ];
        }

        if($bucketObjectsContent !== null) {
            $s3client->deleteObjects([
                'Bucket' => $this->getAwsS3BucketName(),
                'Delete' => [
                    'Objects' => $bucketObjectsContent,
                ],
            ]);
        }
    }

    public function getS3Client(): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region'  => $this->getAwsS3Region(),
            'credentials' => array(
                'key'    => $this->getAwsAccessKeyId(),
                'secret' => $this->getAwsSecretAccessKey(),
            )
        ]);
    }
}
