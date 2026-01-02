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
     * @throws InvalidConfigurationException
     */
    public function createCommandLine(Configuration $configuration, Commands $command): string
    {
        $callable = ['MuckiRestic\Library\CommandLine\Commands\\'.$command->value, 'getCommandLine'];
        if (!is_callable($callable)) {
            throw new InvalidConfigurationException('Invalid command class or method.');
        }

        return call_user_func($callable, $configuration);
    }

    public function createEnvParameters(Configuration $configuration, Commands $command): array
    {
        $callable = ['MuckiRestic\Library\CommandLine\Commands\\'.$command->value, 'getEnvParameters'];
        if (!is_callable($callable)) {
            throw new InvalidConfigurationException('Invalid EnvParameters class or method.');
        }

        return call_user_func($callable, $configuration);
    }
}
