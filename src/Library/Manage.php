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

use MuckiRestic\ResultParser\CheckResultParser;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Core\Commands;
use MuckiRestic\Service\Helper;

class Manage extends Configuration
{
    public function getSnapshots(): ResultEntity
    {
        if($this->checkInputParametersByCommand(Commands::SNAPSHOTS)) {

            $process = $this->createProcess(Commands::SNAPSHOTS);
            $process->run();

            $checkResult = CheckResultParser::textParserResult($process->getOutput());
            $checkResult->setCommandLine($process->getCommandLine());
            $checkResult->setStatus($process->getStatus());
            $checkResult->setStartTime($process->getStartTime());
            $checkResult->setEndTime($process->getLastOutputTime());
            $checkResult->setDuration();
            $checkResult->setOutput($process->getOutput());

            return $checkResult;

        } else {
            throw new InvalidConfigurationException('Invalid configuration');
        }
    }
}