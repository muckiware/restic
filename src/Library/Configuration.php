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
use MuckiRestic\Library\CommandLine\CommandLineFactory;

abstract class Configuration extends Client
{
    protected string $resticBinaryPath = 'restic';

    protected string $repositoryPath;
    protected string $repositoryPassword;

    protected ?string $backupPath = null;

    /**
     * forget command parameters
     * @var int
     */
    protected int $keepDaily=7;
    protected int $keepWeekly=5;
    protected int $keepMonthly=12;
    protected int $keepYearly=75;

    protected bool $skipPrepareBackup=false;
    protected bool $compress=true;
    protected string $restoreItem = 'latest';
    protected string $restoreTarget;

    protected bool $jsonOutput = true;
    protected ?string $snapshotId;

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

    public function getRepositoryPath(): string
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

    public function getKeepDaily(): int
    {
        return $this->keepDaily;
    }

    public function setKeepDaily(int $keepDaily): void
    {
        $this->keepDaily = $keepDaily;
    }

    public function getKeepWeekly(): int
    {
        return $this->keepWeekly;
    }

    public function setKeepWeekly(int $keepWeekly): void
    {
        $this->keepWeekly = $keepWeekly;
    }

    public function getKeepMonthly(): int
    {
        return $this->keepMonthly;
    }

    public function setKeepMonthly(int $keepMonthly): void
    {
        $this->keepMonthly = $keepMonthly;
    }

    public function getKeepYearly(): int
    {
        return $this->keepYearly;
    }

    public function setKeepYearly(int $keepYearly): void
    {
        $this->keepYearly = $keepYearly;
    }

    public function isSkipPrepareBackup(): bool
    {
        return $this->skipPrepareBackup;
    }

    public function setSkipPrepareBackup(bool $skipPrepareBackup): void
    {
        $this->skipPrepareBackup = $skipPrepareBackup;
    }

    public function isCompress(): bool
    {
        return $this->compress;
    }

    public function setCompress(bool $compress): void
    {
        $this->compress = $compress;
    }

    public function getRestoreItem(): string
    {
        return $this->restoreItem;
    }

    public function setRestoreItem(string $restoreItem): void
    {
        $this->restoreItem = $restoreItem;
    }

    public function getRestoreTarget(): string
    {
        return $this->restoreTarget;
    }

    public function setRestoreTarget(string $restoreTarget): void
    {
        $this->restoreTarget = $restoreTarget;
    }

    public function isJsonOutput(): bool
    {
        return $this->jsonOutput;
    }

    public function setJsonOutput(bool $jsonOutput): void
    {
        $this->jsonOutput = $jsonOutput;
    }

    public function getSnapshotId(): ?string
    {
        return $this->snapshotId;
    }

    public function setSnapshotId(?string $snapshotId): void
    {
        $this->snapshotId = $snapshotId;
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
        $commandLineFactory = new CommandLineFactory();
        return $commandLineFactory->createCommandLine($this, $command);
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function createProcess(Commands $commands): Process
    {
        return $this->getProcess($this->getCommandStringByCommand($commands));
    }
}
