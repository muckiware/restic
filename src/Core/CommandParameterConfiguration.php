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
namespace MuckiRestic\Core;

use JsonMapper;
use MuckiRestic\Core\Defaults;
use MuckiRestic\Entity\CommandEntity;
use MuckiRestic\Entity\ParameterEntity;
use MuckiRestic\Exception\InvalidConfigurationException;
class CommandParameterConfiguration
{
    /**
     * @throws \Exception
     */
    public function getCommandParameterConfigurationByCommand(string $inputCommand): ?CommandEntity
    {
        $commandEntity = null;
        foreach ($this->readCommandParameterConfigurationFile() as $configCommand) {

            if($configCommand->command === $inputCommand) {

                $commandParameters = $configCommand->parameters;
                unset($configCommand->parameters);
                $mapper = new JsonMapper();

                /** @var CommandEntity $commandEntity */
                $commandEntity = $mapper->map($configCommand, new CommandEntity());
                foreach ($commandParameters as $commandParameter) {
                    $commandEntity->addParameter($mapper->map($commandParameter, new ParameterEntity()));
                }
            }
        }

        return $commandEntity;
    }

    public function readCommandParameterConfigurationFile(): array
    {
        $filePath = __DIR__ . '/'.Defaults::DEFAULT_COMMAND_PARAMETER_CONFIGURATION;
        if (!file_exists($filePath)) {
            throw new \Exception(Defaults::DEFAULT_COMMAND_PARAMETER_CONFIGURATION.' not found: '.$filePath);
        }

        $jsonContent = file_get_contents($filePath);
        $dataArray = json_decode($jsonContent);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                'Problem to read '.Defaults::DEFAULT_COMMAND_PARAMETER_CONFIGURATION.': ' . json_last_error_msg()
            );
        }

        return $dataArray;
    }
}