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
namespace MuckiRestic\Library;

use Symfony\Component\Process\Process;

use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\RepositoryLocationTypes;

class Manage extends Configuration
{
    public function getSnapshots(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->getSnapshots($repositoryLocationTypes);
    }

    public function removeSnapshots(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->removeSnapshots($repositoryLocationTypes);
    }

    public function removeSnapshotById(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->removeSnapshotById($repositoryLocationTypes);
    }

    public function resultsExecution(Process $process, bool $isJsonOutput, RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->resultsExecution($process, $isJsonOutput, $repositoryLocationTypes);
    }

    public function executePrune(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->executePrune($repositoryLocationTypes);
    }

    public function getRepositoryStats(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->getRepositoryStats($repositoryLocationTypes);
    }

    /**
     * Method to create an instance of BackupFactory and copy current configuration properties.
     *
     * @return ManageFactory
     */
    private function createFactoryInstance(): ManageFactory
    {
        $manageFactory = new ManageFactory();
        $ref = new \ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {

            if(!$prop->isInitialized($this) || $prop->isStatic()) {
                continue;
            }
            $prop->setValue($manageFactory, $prop->getValue($this));
        }
        return $manageFactory;
    }
}