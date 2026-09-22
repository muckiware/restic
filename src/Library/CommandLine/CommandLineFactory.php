<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Library\CommandLine;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Core\Commands;
use MuckiRestic\Exception\InvalidConfigurationException;

class CommandLineFactory
{
    /**
     * @return list<string>
     * @throws InvalidConfigurationException
     */
    public function createCommandLine(Configuration $configuration, Commands $command): array
    {
        $callable = ['MuckiRestic\Library\CommandLine\Commands\\'.$command->value, 'getCommandLine'];
        if (!is_callable($callable)) {
            throw new InvalidConfigurationException('Invalid command class or method.');
        }

        $arguments = call_user_func($callable, $configuration);
        if (!is_array($arguments)) {
            throw new InvalidConfigurationException(
                'Command '.$command->value.' did not return an argument list.'
            );
        }

        $commandLine = [];
        foreach ($arguments as $argument) {
            if (!is_string($argument)) {
                throw new InvalidConfigurationException(
                    'Command '.$command->value.' returned a non-string argument.'
                );
            }
            $commandLine[] = $argument;
        }

        return $commandLine;
    }

    /**
     * @param Configuration $configuration
     * @param Commands $command
     * @return array<string,string>
     * @throws InvalidConfigurationException
     */
    public function createEnvParameters(Configuration $configuration, Commands $command): array
    {
        $callable = ['MuckiRestic\Library\CommandLine\Commands\\'.$command->value, 'getEnvParameters'];
        if (!is_callable($callable)) {
            throw new InvalidConfigurationException('Invalid EnvParameters class or method.');
        }

        return call_user_func($callable, $configuration);
    }
}
