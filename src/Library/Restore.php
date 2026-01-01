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
use Symfony\Component\Process\Exception\ProcessFailedException;
use JsonMapper;

use MuckiRestic\ResultParser\RestoreResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;
use MuckiRestic\Entity\Result\ResticResponse\Snapshot;
use MuckiRestic\Entity\Result\ResticResponse\Summary;
use MuckiRestic\Service\Json;
use MuckiRestic\Core\RepositoryLocationTypes;

class Restore extends Configuration
{
    /**
     * @throws InvalidRepLocationException
     */
    public function createRestore(RepositoryLocationTypes $repositoryLocationTypes=RepositoryLocationTypes::LOCAL, bool $overwrite=false): ResultEntity
    {
        return $this->createFactoryInstance()->createRestore($overwrite, $repositoryLocationTypes);
    }

    /**
     * Method to create an instance of BackupFactory and copy current configuration properties.
     *
     * @return RestoreFactory
     */
    private function createFactoryInstance(): RestoreFactory
    {
        $restoreFactory = new RestoreFactory();
        $ref = new \ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {

            if(!$prop->isInitialized($this) || $prop->isStatic()) {
                continue;
            }
            $prop->setValue($restoreFactory, $prop->getValue($this));
        }
        return $restoreFactory;
    }
}