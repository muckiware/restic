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
use JsonMapper;

use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;
use MuckiRestic\Entity\Result\ResticResponse\Snapshot;
use MuckiRestic\Entity\Result\ResticResponse\Summary;
use MuckiRestic\Service\Json;

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

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

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

    public function executeForget(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::FORGET)) {

            $process = $this->createProcess(Commands::FORGET);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $resultOutput = Json::decode($process->getOutput());

            $forgetResult = new ResultEntity();
            $forgetResult->setCommandLine($process->getCommandLine());
            $forgetResult->setStatus($process->getStatus());
            $forgetResult->setStartTime($process->getStartTime());
            $forgetResult->setEndTime($process->getLastOutputTime());
            $forgetResult->setDuration();
            $forgetResult->setResticResponse($resultOutput);
            $forgetResult->setOutput($process->getOutput());

            return $forgetResult;

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }
}