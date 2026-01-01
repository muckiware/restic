<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2025 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Library;

use MuckiRestic\Exception\InvalidRepLocationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\RepositoryLocationTypes;

class Backup extends Configuration
{
    /**
     * @throws InvalidRepLocationException
     */
    public function createRepository(bool $overwrite=false, RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->createRepository($overwrite, $repositoryLocationTypes);
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function createBackup(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->createBackup($repositoryLocationTypes);
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function prepareBackup(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): void
    {
        $this->createFactoryInstance()->prepareBackup($repositoryLocationTypes);
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function checkBackup(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        return $this->createFactoryInstance()->checkBackup($repositoryLocationTypes);
    }

    /**
     * Method to create an instance of BackupFactory and copy current configuration properties.
     *
     * @return BackupFactory
     */
    private function createFactoryInstance(): BackupFactory
    {
        $backupFactory = new BackupFactory();
        $ref = new \ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {

            if(!$prop->isInitialized($this) || $prop->isStatic()) {
                continue;
            }
            $prop->setValue($backupFactory, $prop->getValue($this));
        }
        return $backupFactory;
    }
}
