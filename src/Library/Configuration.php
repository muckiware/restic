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

    public function setBinaryPath(string $path): void
    {
        $this->resticBinaryPath = $path;
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

    /**
     * @throws \Exception
     */
    public function checkInputParametersByCommand(string $inputCommand): bool
    {
        $commandParameterConfiguration = CommandParameterConfiguration::getCommandParameterConfigurationByCommand(
            $inputCommand
        );

        foreach ($commandParameterConfiguration->getParameters() as $parameter) {
            if ($parameter->isRequired() && empty($this->{$parameter->getName()})) {
                throw new InvalidConfigurationException('Missing required parameter '.$parameter->getName());
            }
        }

        return true;
    }
}
