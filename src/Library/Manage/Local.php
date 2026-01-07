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
namespace MuckiRestic\Library\Manage;

use JsonMapper;
use Doctrine\Common\Collections\ArrayCollection;
use MuckiRestic\Library\Configuration;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use MuckiRestic\Core\Commands;
use MuckiRestic\Entity\Result\ResticResponse\Snapshot;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Service\Json;
use MuckiRestic\Library\ManageInterface;
use MuckiRestic\Service\Helper;

class Local extends Configuration implements ManageInterface
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

            $snapshots = new ArrayCollection();

            if($this->isJsonOutput()) {
                foreach (json_decode($process->getOutput()) as $snapshot) {

                    $mapper = new JsonMapper();
                    $snapshots->add($mapper->map($snapshot, new Snapshot()));
                }
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

    /**
     * @throws InvalidConfigurationException
     * @throws ProcessFailedException
     * @throws \Exception
     */
    public function removeSnapshots(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::FORGET)) {

            $process = $this->createProcess(Commands::FORGET);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return $this->resultsExecution($process, $this->isJsonOutput());

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }

    /**
     * @throws InvalidConfigurationException
     * @throws ProcessFailedException
     * @throws \Exception
    */
    public function removeSnapshotById(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::SINGLE_FORGET)) {

            $process = $this->createProcess(Commands::SINGLE_FORGET);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return $this->resultsExecution($process, false, true);

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function executePrune(): ResultEntity
    {
        if(!$this->checkInputParametersByCommand(Commands::PRUNE)) {
            throw new InvalidConfigurationException('Invalid configuration');
        }

        $process = $this->createProcess(Commands::PRUNE);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->resultsExecution($process, false, true);
    }

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function getRepositoryStats(): ResultEntity
    {
        if(!$this->checkInputParametersByCommand(Commands::STATS)) {
            throw new InvalidConfigurationException('Invalid configuration');
        }

        $process = $this->createProcess(Commands::STATS);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->resultsExecution($process, $this->isJsonOutput(), true);
    }

    /**
     * @param Process $process
     * @param bool $isJsonOutput
     * @param bool $skipVersionCheck
     * @return ResultEntity
     */
    public function resultsExecution(Process $process, bool $isJsonOutput, bool $skipVersionCheck=false): ResultEntity
    {
        $currentBinaryVersion = $this->getResticVersion()->getResticResponse()->getVersion();
        if($isJsonOutput && Helper::checkBinaryVersion($currentBinaryVersion, $skipVersionCheck)) {
            $resultOutput = Json::decode($process->getOutput());
        } else {
            $resultOutput = $process->getOutput();
        }

        $forgetResult = new ResultEntity();
        $forgetResult->setCommandLine($process->getCommandLine());
        $forgetResult->setStatus($process->getStatus());
        $forgetResult->setStartTime($process->getStartTime());
        $forgetResult->setEndTime($process->getLastOutputTime());
        $forgetResult->setDuration();
        $forgetResult->setResticResponse($resultOutput);
        $forgetResult->setOutput($process->getOutput());

        return $forgetResult;
    }
}
