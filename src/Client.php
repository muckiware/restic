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
namespace MuckiRestic;

use Symfony\Component\Process\Process;
use JsonMapper;

use MuckiRestic\Entity\Result\ResultEntity;
use MuckiRestic\Entity\Result\ResticResponse\Version;
use MuckiRestic\ResultParser\VersionResultParser;
use MuckiRestic\ResultParser\OutputParser;

abstract class Client
{
    protected string $resticBinaryPath = '';

    final public function __construct()
    {}
    public static function create(): static
    {
        return new static();
    }

    public function getProcess(string $command): Process
    {
        return Process::fromShellCommandline($command, null, null, null, 1000);
    }

    public function requestVersion(string $command): Process
    {
        $process = $this->getProcess($this->resticBinaryPath.' '.$command);
        $process->run();

        return $process;
    }
    public function getResticVersion(): ResultEntity
    {
        $process = $this->requestVersion('version');
        $version = $this->createVersion($process->getOutput());
        if (version_compare($version->getVersion(), '0.17.0', '>=')) {

            $versionRawResult = OutputParser::fixJsonOutput($this->requestVersion('version --json')->getOutput());
            $mapper = new JsonMapper();
            $version = $mapper->map(json_decode($versionRawResult)[0], new Version());
        }

        $versionResultEntity = new ResultEntity();
        $versionResultEntity->setCommandLine($process->getCommandLine());
        $versionResultEntity->setResticResponse($version);
        $versionResultEntity->setOutput($process->getOutput());

        return $versionResultEntity;
    }

    public function createVersion(string $processOutput): Version
    {
        $versionResult = VersionResultParser::getVersionResultFromTextOutput($processOutput);
        if(!$versionResult) {
            throw new \RuntimeException('Not supported restic version' . $processOutput);
        }
        $version = new Version();
        $version->setVersion($versionResult);

        $goVersionResult = VersionResultParser::getGoVersionResultFromTextOutput($processOutput);
        if($goVersionResult) {
            $version->setGoVersion($goVersionResult);
        }

        return $version;
    }

    abstract public function setBinaryPath(string $path): void;
}
