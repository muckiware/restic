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

use MuckiRestic\Client;
use MuckiRestic\Core\CommandParameterConfiguration;
use MuckiRestic\Exception\InvalidConfigurationException;

abstract class Configuration extends Client
{
    protected string $resticBinaryPath = 'restic';

    protected string $repositoryPath;

    protected string $repositoryPassword;

    protected string $backupPath;

    public function setBinaryPath(string $path): void
    {
        $this->resticBinaryPath = $path;
    }

    public function getBinaryPath(): string
    {
        return $this->resticBinaryPath;
    }

    public function setRepositoryPath(string $path): void
    {
        $this->repositoryPath = $path;
    }

    protected function getRepositoryPath(): string
    {
        return $this->repositoryPath;
    }

    public function getRepositoryPassword(): string
    {
        return $this->repositoryPassword;
    }

    public function setRepositoryPassword(string $repositoryPassword): void
    {
        $this->repositoryPassword = $repositoryPassword;
    }

    public function getBackupPath(): string
    {
        return $this->backupPath;
    }

    public function setBackupPath(string $backupPath): void
    {
        $this->backupPath = $backupPath;
    }

    /**
     * @throws \Exception
     */
    public function checkInputParametersByCommand(string $inputCommand): bool
    {
        $commandConfig = new CommandParameterConfiguration();
        $commandParameterConfiguration = $commandConfig->getCommandParameterConfigurationByCommand(
            $inputCommand
        );

        if($commandParameterConfiguration === null) {
            throw new InvalidConfigurationException('Invalid command '.$inputCommand);
        }

        foreach ($commandParameterConfiguration->getParameters() as $parameter) {

            if ($parameter->isRequired() && empty($this->{$parameter->getName()})) {
                throw new InvalidConfigurationException('Missing required parameter '.$parameter->getName());
            }
        }

        return true;
    }
}
