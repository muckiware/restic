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

class BackupFactory extends Configuration
{
    /**
     * @throws InvalidRepLocationException
     * @throws \Exception
     */
    public function createRepository(bool $overwrite=false, RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        $className = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'createRepository';

        return $this->createClassByName($className, $method)->$method($overwrite);
    }

    /**
     * @throws InvalidRepLocationException
     * @throws \Exception
     */
    public function createBackup(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        $className = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'createBackup';

        return $this->createClassByName($className, $method)->$method();
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function prepareBackup(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): void
    {
        $this->runUnlockCommand($repositoryLocationTypes);
        $this->runPruneCommand($repositoryLocationTypes);
    }

    /**
     * @throws InvalidRepLocationException
     * @throws \Exception
     */
    public function checkBackup(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        $className = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'checkBackup';

        return $this->createClassByName($className, $method)->$method();
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function runUnlockCommand(RepositoryLocationTypes $repositoryLocationTypes): void
    {
        $className = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'runUnlockCommand';

        $this->createClassByName($className, $method)->$method();
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function runPruneCommand(RepositoryLocationTypes $repositoryLocationTypes): void
    {
        $className = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = __METHOD__;

        $this->createClassByName($className, $method)->$method();
    }

    /**
     * @throws InvalidRepLocationException
     */
    private function createClassByName(string $className, string $method): mixed
    {
        if (!class_exists($className)) {
            throw new InvalidRepLocationException('Class not found '.$className);
        }
        if (!method_exists($className, $method)) {
            throw new InvalidRepLocationException('Method not found: '.$method.' in '.$className);
        }

        $rm = new \ReflectionMethod($className, $method);
        if (!$rm->isPublic()) {
            throw new InvalidRepLocationException('Method is not public: '.$method);
        }

        return $this->createFactoryInstance($className);
    }

    /**
     * Method to create an instance of Factory class by $className and copy all current configuration properties.
     *
     * @param string $className
     * @return mixed
     */
    private function createFactoryInstance(string $className): mixed
    {
        $class = new $className();
        $ref = new \ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {

            if(!$prop->isInitialized($this) || $prop->isStatic()) {
                continue;
            }
            $prop->setValue($class, $prop->getValue($this));
        }
        return $class;
    }
}
