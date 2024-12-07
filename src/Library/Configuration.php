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

use MuckiRestic\Client;
use MuckiRestic\Core\CommandParameterConfiguration;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Core\Commands;

abstract class Configuration extends Client
{
    protected string $resticBinaryPath = 'restic';

    protected string $repositoryPath;

    protected string $repositoryPassword;

    protected ?string $backupPath = null;

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

    public function getBackupPath(): ?string
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
    public function checkInputParametersByCommand(Commands $commands): bool
    {
        $commandConfig = new CommandParameterConfiguration();
        $commandParameterConfiguration = $commandConfig->getCommandParameterConfigurationByCommand(
            $commands
        );

        if($commandParameterConfiguration === null) {
            throw new InvalidConfigurationException('Invalid command '.$commands->value);
        }

        foreach ($commandParameterConfiguration->getParameters() as $parameter) {

            if ($parameter->isRequired() && empty($this->{$parameter->getName()})) {
                throw new InvalidConfigurationException('Missing required parameter '.$parameter->getName());
            }
        }

        if($this->repositoryPath && $this->backupPath && $this->repositoryPath === $this->backupPath) {
            throw new InvalidConfigurationException('Repository path and backup path should not be the same');
        }

        return true;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function getCommandStringByCommand(Commands $command): string
    {
        switch ($command->value) {

            case 'Init':
                $commandString = sprintf(
                    'export RESTIC_PASSWORD="%s"'."\n".'%s init --repo %s',
                    $this->repositoryPassword,
                    $this->resticBinaryPath,
                    $this->repositoryPath
                );
                break;
            case 'Backup':
                $commandString = sprintf(
                    'export RESTIC_PASSWORD="%s"'."\n".'export RESTIC_REPOSITORY="%s"'."\n".'%s backup %s',
                    $this->repositoryPassword,
                    $this->repositoryPath,
                    $this->resticBinaryPath,
                    $this->repositoryPath
                );
                break;
            case 'Check':
                $commandString = sprintf(
                    'export RESTIC_PASSWORD="%s"'."\n".'export RESTIC_REPOSITORY="%s"'."\n".'%s check',
                    $this->repositoryPassword,
                    $this->repositoryPath,
                    $this->resticBinaryPath
                );
                break;
            default:
                throw new InvalidConfigurationException('Invalid command for to create restic command'.$command->value);
        }

        return $commandString;
    }

    public function createProcess(Commands $commands): Process
    {
        return $this->getProcess($this->getCommandStringByCommand($commands));
    }
}
