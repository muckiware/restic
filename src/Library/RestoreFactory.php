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

use MuckiRestic\Exception\InvalidRepLocationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\RepositoryLocationTypes;

class RestoreFactory extends Configuration
{
    /**
     * @throws InvalidRepLocationException
     * @throws \Exception
     */
    public function createRestore(bool $overwrite=false, RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL): ResultEntity
    {
        $className = 'MuckiRestic\Library\Restore\\'.$repositoryLocationTypes->value;
        $method = 'createRestore';

        return $this->createClassByName($className, $method)->$method($overwrite);
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
