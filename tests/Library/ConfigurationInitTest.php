<?php

namespace Library;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Core\CommandParameterConfiguration;
use MuckiRestic\Test\TestData;
use MuckiRestic\Entity\CommandEntity;
use MuckiRestic\Entity\ParameterEntity;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Core\Commands;

class ConfigurationInitTest extends TestCase
{
    public function testCheckInputParametersByCommand(): void
    {
        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with(Commands::INIT)
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPassword('password');
        $configuration->setRepositoryPath('/var/repository');

        $result = $configuration->checkInputParametersByCommand(Commands::INIT);
        $this->assertTrue($result, 'Check input parameters by command Backup should return true');
    }

    public function testCheckInputParametersByCommandMissingPassword(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Missing required parameter repositoryPassword'
        );

        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with(Commands::INIT)
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPath('/var/repository');

        $result = $configuration->checkInputParametersByCommand(Commands::INIT);
        $this->assertTrue(true);
    }

    public function testCheckInputParametersByCommandMissingRepositoryPath(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Missing required parameter repositoryPath'
        );

        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with(Commands::INIT)
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPassword('password');

        $result = $configuration->checkInputParametersByCommand(Commands::INIT);
        $this->assertTrue(true);
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