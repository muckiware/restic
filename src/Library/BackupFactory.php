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

use Symfony\Component\Process\Exception\ProcessFailedException;

use MuckiRestic\ResultParser\InitResultParser;
use MuckiRestic\ResultParser\BackupResultParser;
use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidRepLocationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;
use MuckiRestic\Service\Json;
use MuckiRestic\Core\RepositoryLocationTypes;

class BackupFactory extends Configuration
{
    /**
     * @throws InvalidRepLocationException
     * @throws \Exception
     */
    public function createRepository(bool $overwrite=false, RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        $class = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'createRepository';

        return $this->callMethod($class, $method)->$method($overwrite);
    }

    /**
     * @throws InvalidRepLocationException
     * @throws \Exception
     */
    public function createBackup(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        $class = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'createBackup';

        return $this->callMethod($class, $method)->$method();
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
        $class = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'checkBackup';

        return $this->callMethod($class, $method)->$method();
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function runUnlockCommand(RepositoryLocationTypes $repositoryLocationTypes): void
    {
        $class = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'runUnlockCommand';

        $this->callMethod($class, $method)->$method();
    }

    /**
     * @throws InvalidRepLocationException
     */
    public function runPruneCommand(RepositoryLocationTypes $repositoryLocationTypes): void
    {
        $class = 'MuckiRestic\Library\Backup\\'.$repositoryLocationTypes->value;
        $method = 'runPruneCommand';

        $this->callMethod($class, $method)->$method();
    }

    /**
     * @throws InvalidRepLocationException
     */
    private function callMethod(string $class, string $method): mixed
    {
        if (!class_exists($class)) {
            throw new InvalidRepLocationException('Class not found '.$class);
        }
        if (!method_exists($class, $method)) {
            throw new InvalidRepLocationException('Method not found: '.$method.' in '.$class);
        }

        $rm = new \ReflectionMethod($class, $method);
        if (!$rm->isPublic()) {
            throw new InvalidRepLocationException('Method is not public: '.$method);
        }

        return $this->createFactoryInstance($class);
    }

    /**
     * Method to create an instance of Factory and copy current configuration properties.
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
