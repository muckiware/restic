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

use Symfony\Component\Process\Process;

use MuckiRestic\ResultParser\BackupResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\BackupResultEntity;

class Backup extends Configuration
{
    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createRepository(): string
    {
        if($this->checkInputParametersByCommand('Init')) {

            $process = $this->getProcess(
                sprintf('export RESTIC_PASSWORD="%s"'."\n".'%s init --repo %s',
                    $this->repositoryPassword,
                    $this->resticBinaryPath,
                    $this->repositoryPath
                )
            );
            $process->run();

            return $process->getOutput();
        }

        throw new InvalidConfigurationException('Invalid configuration');
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createBackupStrResults(): string
    {
        if($this->checkInputParametersByCommand('Backup')) {

            $process = $this->createProcess();
            $process->run();
            return $process->getOutput();

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createBackup(): BackupResultEntity
    {
        if($this->checkInputParametersByCommand('Backup')) {

            $process = $this->createProcess();
            $process->run();

            return BackupResultParser::textParserBackupResult($process->getOutput());
        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }

    public function createProcess(): Process
    {
        return $this->getProcess(
            sprintf('export RESTIC_PASSWORD="%s"'."\n".'export RESTIC_REPOSITORY="%s"'."\n".'%s backup %s',
                $this->repositoryPassword,
                $this->repositoryPath,
                $this->resticBinaryPath,
                $this->repositoryPath
            )
        );
    }
}