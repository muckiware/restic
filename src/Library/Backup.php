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
namespace MuckiRestic\Library;

use Symfony\Component\Process\Exception\ProcessFailedException;

use MuckiRestic\ResultParser\InitResultParser;
use MuckiRestic\ResultParser\BackupResultParser;
use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;
use MuckiRestic\Service\Json;

class Backup extends Configuration
{
    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createRepository(bool $overwrite=false): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::INIT)) {

            if($overwrite && is_dir($this->getRepositoryPath())) {
                Helper::deleteDirectory($this->getRepositoryPath());
            }

            $process = $this->createProcess(Commands::INIT);
            $process->run();

            $initOutput = Json::decode($process->getOutput());

            $initResult = new ResultEntity();
            $initResult->setCommandLine($process->getCommandLine());
            $initResult->setStatus($process->getStatus());
            $initResult->setStartTime($process->getStartTime());
            $initResult->setEndTime($process->getLastOutputTime());
            $initResult->setDuration();
            $initResult->setOutput($process->getOutput());
            $initResult->setResticResponse($initOutput);

            return $initResult;

        }

        throw new InvalidConfigurationException('Invalid configuration');
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createBackup(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::BACKUP)) {

            $this->prepareBackup();
            $process = $this->createProcess(Commands::BACKUP);
            $process->run();

            $backupOutput = Json::decode(BackupResultParser::fixBackupJsonOutput($process->getOutput()));

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
}
