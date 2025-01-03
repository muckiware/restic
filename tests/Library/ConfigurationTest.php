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
namespace MuckiRestic\Test\Library;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Core\CommandParameterConfiguration;
use MuckiRestic\Test\TestData;
use MuckiRestic\Entity\CommandEntity;
use MuckiRestic\Entity\ParameterEntity;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Core\Commands;

class ConfigurationTest extends TestCase
{
    public function testCheckGetResticBinaryPath(): void
    {
        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setBinaryPath('/var/bin/restic');

        static::assertEquals('/var/bin/restic', $configuration->getBinaryPath(), 'Binary path should be /var/bin/restic');
    }

    public function testCheckGetResticBinaryPathWithoutSetting(): void
    {
        $configuration = $this->getMockForAbstractClass(Configuration::class);

        static::assertEquals('restic', $configuration->getBinaryPath(), 'Binary path should just be restic');
    }

    public function testCheckInputParametersByInvalidCommand(): void
    {
        $invalidCommand = 'TestCommand';
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Invalid command '.Commands::TEST_COMMAND->value
        );
        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPassword('password');
        $configuration->setRepositoryPath('/var/repository');
        $configuration->setBackupPath('/var/backup');

        $result = $configuration->checkInputParametersByCommand(Commands::TEST_COMMAND);
        $this->assertTrue($result, 'Check input parameters by command Backup should return true');
    }

    protected function getCommandEntityParameters(): CommandEntity
    {
        $commandEntity = new CommandEntity();
        $parameterEntity = new ParameterEntity();
        $parameterEntity->setName('repositoryPath');
        $parameterEntity->setType('string');
        $parameterEntity->setRequired(true);
        $parameters[] = $parameterEntity;
        $commandEntity->setParameters($parameters);

        return $commandEntity;
    }
}