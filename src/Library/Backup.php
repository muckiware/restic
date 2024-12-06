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
    public function createRepository(bool $overwrite=false, bool $stringOutput=false): ResultEntity|string
    {
        if($this->checkInputParametersByCommand(Commands::INIT)) {

            if($overwrite && is_dir($this->getRepositoryPath())) {
                Helper::deleteDirectory($this->getRepositoryPath());
            }

            $process = $this->createProcess(Commands::INIT);
            $process->run();

            if($stringOutput) {
                return $process->getOutput();
            } else {
                $resultEntity = InitResultParser::textParserResult($process->getOutput());
                $resultEntity->setCommandLine($process->getCommandLine());
                $resultEntity->setStatus($process->getStatus());
                $resultEntity->setStartTime($process->getStartTime());
                $resultEntity->setEndTime($process->getLastOutputTime());
                $resultEntity->setDuration();

                return $resultEntity;
            }
        }

        throw new InvalidConfigurationException('Invalid configuration');
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createBackup(bool $stringOutput=false): ResultEntity|string
    {
        if($this->checkInputParametersByCommand(Commands::BACKUP)) {

            $process = $this->createProcess(Commands::BACKUP);
            $process->run();

            if($stringOutput) {
                return $process->getOutput();
            } else {
                return BackupResultParser::textParserResult($process->getOutput());
            }

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function checkBackup(bool $stringOutput=false): ResultEntity|string
    {
        if($this->checkInputParametersByCommand(Commands::CHECK)) {

            $process = $this->createProcess(Commands::CHECK);
            $process->run();

            if($stringOutput) {
                return $process->getOutput();
            } else {
                return CheckResultParser::textParserResult($process->getOutput());
            }

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }
}
