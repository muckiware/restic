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

class Restore extends Configuration
{
    public function createRestore(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::RESTORE)) {

            $process = $this->createProcess(Commands::RESTORE);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $resticVersion = $this->getResticVersion()->getResticResponse()->getVersion();
            if($this->isJsonOutput() && version_compare($resticVersion, '0.16.0', '>=')) {
                $resultOutput = Json::decode(RestoreResultParser::fixJsonOutput($process->getOutput()));
            } else {
                $resultOutput = array($process->getOutput());
            }

            $restoreResult = new ResultEntity();
            $restoreResult->setCommandLine($process->getCommandLine());
            $restoreResult->setStatus($process->getStatus());
            $restoreResult->setStartTime($process->getStartTime());
            $restoreResult->setEndTime($process->getLastOutputTime());
            $restoreResult->setDuration();
            $restoreResult->setResticResponse($resultOutput);
            $restoreResult->setOutput($process->getOutput());

            return $restoreResult;

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }
}