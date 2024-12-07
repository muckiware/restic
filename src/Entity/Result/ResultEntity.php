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
namespace MuckiRestic\Entity\Result;

use MuckiRestic\Entity\DataSet;

class ResultEntity extends DataSet
{
    protected ?SnapshotsResultEntity $snapshots;
    protected ?FilesDirsResultEntity $files;
    protected ?FilesDirsResultEntity $directories;

    /**
     * @var array<mixed>|null
     */
    protected ?array $processed;

    protected ?string $commandLine;

    protected ?string $status;

    protected ?float $duration;
    protected ?float $startTime;
    protected ?float $endTime;

    protected ?string $output;

    public function getSnapshots(): ?SnapshotsResultEntity
    {
        return $this->snapshots;
    }

    public function setSnapshots(SnapshotsResultEntity $snapshots): void
    {
        $this->snapshots = $snapshots;
    }

    public function getFiles(): ?FilesDirsResultEntity
    {
        return $this->files;
    }

    public function setFiles(FilesDirsResultEntity $files): void
    {
        $this->files = $files;
    }

    public function getDirectories(): ?FilesDirsResultEntity
    {
        return $this->directories;
    }

    public function setDirectories(FilesDirsResultEntity $directories): void
    {
        $this->directories = $directories;
    }

    /**
     * @return array<mixed>|null
     */
    public function getProcessed(): ?array
    {
        return $this->processed;
    }

    /**
     * @param array<mixed> $processed
     */
    public function setProcessed(array $processed): void
    {
        $this->processed = $processed;
    }

    public function addProcessed(string $processed): void
    {
        $this->processed[] = $processed;
    }

    public function getCommandLine(): ?string
    {
        return $this->commandLine;
    }

    public function setCommandLine(?string $commandLine): void
    {
        $this->commandLine = $commandLine;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    public function setStartTime(?float $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?float
    {
        return $this->endTime;
    }

    public function setEndTime(?float $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getDuration(): ?float
    {
        if($this->startTime && $this->endTime) {
            return $this->endTime - $this->startTime;
        }

        return null;
    }

    public function setDuration(): void
    {
        if($this->startTime && $this->endTime) {
            $this->duration = $this->endTime - $this->startTime;
        }
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): void
    {
        $this->output = $output;
    }
}
