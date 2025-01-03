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
namespace Library;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Core\CommandParameterConfiguration;
use MuckiRestic\Test\TestData;
use MuckiRestic\Entity\CommandEntity;
use MuckiRestic\Entity\ParameterEntity;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Core\Commands;

class ConfigurationBackupTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCheckInputParametersByCommand(): void
    {
        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with('Backup')
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPassword('password');
        $configuration->setRepositoryPath('/var/repository');
        $configuration->setBackupPath('/var/backup');

        $result = $configuration->checkInputParametersByCommand(Commands::BACKUP);
        $this->assertTrue($result, 'Check input parameters by command Backup should return true');
    }

    /**
     * @throws \Exception
     */
    public function testCheckInputParametersByCommandMissingPassword(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Missing required parameter repositoryPassword'
        );

        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with('Backup')
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPath('/var/repository');
        $configuration->setBackupPath('/var/backup');

        $result = $configuration->checkInputParametersByCommand(Commands::BACKUP);
        $this->assertTrue(true);
    }

    /**
     * @throws \Exception
     */
    public function testCheckInputParametersByCommandMissingRepositoryPath(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Missing required parameter repositoryPath'
        );

        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with(Commands::BACKUP)
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPassword('password');
        $configuration->setBackupPath('/var/backup');

        $result = $configuration->checkInputParametersByCommand(Commands::BACKUP);
        $this->assertTrue(true);
    }

    public function testCheckInputParametersByCommandMissingBackupPath(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Missing required parameter backupPath'
        );

        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with(Commands::BACKUP)
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPassword('password');
        $configuration->setRepositoryPath('/var/repository');

        $result = $configuration->checkInputParametersByCommand(Commands::BACKUP);
        $this->assertTrue(true);
    }

    public function testCheckInputParametersByCommandSamePaths(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Repository path and backup path should not be the same'
        );

        $commandConfigMock = $this->createMock(CommandParameterConfiguration::class);
        $commandConfigMock->method('getCommandParameterConfigurationByCommand')
            ->with(Commands::BACKUP)
            ->willReturn($this->getCommandEntityParameters());

        $configuration = $this->getMockForAbstractClass(Configuration::class);
        $configuration->setRepositoryPassword('password');
        $configuration->setRepositoryPath('/var/repository');
        $configuration->setBackupPath('/var/repository');

        $result = $configuration->checkInputParametersByCommand(Commands::BACKUP);
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