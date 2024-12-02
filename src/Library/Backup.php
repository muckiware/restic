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

use MuckiRestic\ResultParser\BackupResultParser;
use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\BackupResultEntity;
use MuckiRestic\Entity\Result\CheckResultEntity;
use MuckiRestic\Core\Commands;

class Backup extends Configuration
{
    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createRepository(): string
    {
        if($this->checkInputParametersByCommand(Commands::INIT)) {

            $process = $this->createProcess(Commands::INIT);
            $process->run();

            return $process->getOutput();
        }

        throw new InvalidConfigurationException('Invalid configuration');
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createBackup(bool $stringOutput=false): BackupResultEntity|string
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
    public function checkBackup(bool $stringOutput=false): CheckResultEntity|string
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
