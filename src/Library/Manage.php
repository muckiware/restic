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

use JsonMapper;

use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;
use MuckiRestic\Entity\Result\ResticResponse\Snapshot;
use MuckiRestic\Entity\Result\ResticResponse\Summary;

class Manage extends Configuration
{
    /**
     * @throws \JsonMapper_Exception
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function getSnapshots(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::SNAPSHOTS)) {

            $process = $this->createProcess(Commands::SNAPSHOTS);
            $process->run();

            $snapshots = array();
            foreach (json_decode($process->getOutput()) as $snapshot) {

                $mapper = new JsonMapper();
                $snapshots[] = $mapper->map($snapshot, new Snapshot());
            }

            $snapshotsResult = new ResultEntity();
            $snapshotsResult->setCommandLine($process->getCommandLine());
            $snapshotsResult->setStatus($process->getStatus());
            $snapshotsResult->setStartTime($process->getStartTime());
            $snapshotsResult->setEndTime($process->getLastOutputTime());
            $snapshotsResult->setDuration();
            $snapshotsResult->setResticResponse($snapshots);
            $snapshotsResult->setOutput($process->getOutput());

            return $snapshotsResult;

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }
}