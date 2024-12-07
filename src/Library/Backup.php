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

use MuckiRestic\ResultParser\InitResultParser;
use MuckiRestic\ResultParser\BackupResultParser;
use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;

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

            $initResult = InitResultParser::textParserResult($process->getOutput());
            $initResult->setCommandLine($process->getCommandLine());
            $initResult->setStatus($process->getStatus());
            $initResult->setStartTime($process->getStartTime());
            $initResult->setEndTime($process->getLastOutputTime());
            $initResult->setDuration();
            $initResult->setOutput($process->getOutput());

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

            $process = $this->createProcess(Commands::BACKUP);
            $process->run();

            $backupResult = BackupResultParser::textParserResult($process->getOutput());
            $backupResult->setCommandLine($process->getCommandLine());
            $backupResult->setStatus($process->getStatus());
            $backupResult->setStartTime($process->getStartTime());
            $backupResult->setEndTime($process->getLastOutputTime());
            $backupResult->setDuration();
            $backupResult->setOutput($process->getOutput());

            return $backupResult;

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
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
}
