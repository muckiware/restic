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

class BackupResultEntity extends DataSet
{
    protected SnapshotsResultEntity $snapshots;
    protected FilesDirsResultEntity $files;
    protected FilesDirsResultEntity $directories;
    protected array $processed;

    public function getSnapshots(): SnapshotsResultEntity
    {
        return $this->snapshots;
    }

    public function setSnapshots(SnapshotsResultEntity $snapshots): void
    {
        $this->snapshots = $snapshots;
    }

    public function getFiles(): FilesDirsResultEntity
    {
        return $this->files;
    }

    public function setFiles(FilesDirsResultEntity $files): void
    {
        $this->files = $files;
    }

    public function getDirectories(): FilesDirsResultEntity
    {
        return $this->directories;
    }

    public function setDirectories(FilesDirsResultEntity $directories): void
    {
        $this->directories = $directories;
    }

    public function getProcessed(): array
    {
        return $this->processed;
    }

    public function setProcessed(array $processed): void
    {
        $this->processed = $processed;
    }
}
