<?php

namespace MuckiRestic\Entity\Result\ResticResponse;

class Summary
{
    protected \DateTime $backupStart;
    protected \DateTime $backupEnd;
    protected int $filesNew;
    protected int $filesChanged;
    protected int $filesUnmodified;
    protected int $dirsNew;
    protected int $dirsChanged;
    protected int $dirsUnmodified;
    protected int $dataBlobs;
    protected int $treeBlobs;
    protected int $dataAdded;
    protected int $dataAddedPacked;
    protected int $totalFilesProcessed;
    protected int $totalBytesProcessed;

    public function getBackupStart(): \DateTime
    {
        return $this->backupStart;
    }

    /**
     * @throws \Exception
     */
    public function setBackupStart(string $backupStart): void
    {
        $this->backupStart = new \DateTime($backupStart);
    }

    public function getBackupEnd(): \DateTime
    {
        return $this->backupEnd;
    }

    /**
     * @throws \Exception
     */
    public function setBackupEnd(string $backupEnd): void
    {
        $this->backupEnd = new \DateTime($backupEnd);
    }

    public function getFilesNew(): int
    {
        return $this->filesNew;
    }

    public function setFilesNew(int $filesNew): void
    {
        $this->filesNew = $filesNew;
    }

    public function getFilesChanged(): int
    {
        return $this->filesChanged;
    }

    public function setFilesChanged(int $filesChanged): void
    {
        $this->filesChanged = $filesChanged;
    }

    public function getFilesUnmodified(): int
    {
        return $this->filesUnmodified;
    }

    public function setFilesUnmodified(int $filesUnmodified): void
    {
        $this->filesUnmodified = $filesUnmodified;
    }

    public function getDirsNew(): int
    {
        return $this->dirsNew;
    }

    public function setDirsNew(int $dirsNew): void
    {
        $this->dirsNew = $dirsNew;
    }

    public function getDirsChanged(): int
    {
        return $this->dirsChanged;
    }

    public function setDirsChanged(int $dirsChanged): void
    {
        $this->dirsChanged = $dirsChanged;
    }

    public function getDirsUnmodified(): int
    {
        return $this->dirsUnmodified;
    }

    public function setDirsUnmodified(int $dirsUnmodified): void
    {
        $this->dirsUnmodified = $dirsUnmodified;
    }

    public function getDataBlobs(): int
    {
        return $this->dataBlobs;
    }

    public function setDataBlobs(int $dataBlobs): void
    {
        $this->dataBlobs = $dataBlobs;
    }

    public function getTreeBlobs(): int
    {
        return $this->treeBlobs;
    }

    public function setTreeBlobs(int $treeBlobs): void
    {
        $this->treeBlobs = $treeBlobs;
    }

    public function getDataAdded(): int
    {
        return $this->dataAdded;
    }

    public function setDataAdded(int $dataAdded): void
    {
        $this->dataAdded = $dataAdded;
    }

    public function getDataAddedPacked(): int
    {
        return $this->dataAddedPacked;
    }

    public function setDataAddedPacked(int $dataAddedPacked): void
    {
        $this->dataAddedPacked = $dataAddedPacked;
    }

    public function getTotalFilesProcessed(): int
    {
        return $this->totalFilesProcessed;
    }

    public function setTotalFilesProcessed(int $totalFilesProcessed): void
    {
        $this->totalFilesProcessed = $totalFilesProcessed;
    }

    public function getTotalBytesProcessed(): int
    {
        return $this->totalBytesProcessed;
    }

    public function setTotalBytesProcessed(int $totalBytesProcessed): void
    {
        $this->totalBytesProcessed = $totalBytesProcessed;
    }
}
