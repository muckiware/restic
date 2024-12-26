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

abstract class Client
{
    protected string $resticBinaryPath = '';

    protected string $version;

    final public function __construct()
    {}
    public static function create(): static
    {
        return new static();
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getProcess(string $command): Process
    {
        return Process::fromShellCommandline($command, null, null, null, 1000);
    }

    public function getResticVersion(): ResultEntity
    {
        $process = $this->getProcess($this->resticBinaryPath.' version');
        $process->run();

        $versionOutput = $process->getOutput();
        $this->setVersion(VersionResultParser::extractVersion($versionOutput));

        if (version_compare($this->getVersion(), '0.17.0') >= 0) {

            $process = $this->getProcess($this->resticBinaryPath.' version --json');
            $process->run();
            $mapper = new JsonMapper();
            $version = $mapper->map(json_decode($process->getOutput()),new Version());

        } else {
            $version = new Version();
            $version->setVersion($this->getVersion());
        }

        $versionResultEntity = new ResultEntity();
        $versionResultEntity->setCommandLine($process->getCommandLine());
        $versionResultEntity->setResticResponse($version);
        $versionResultEntity->setOutput($process->getOutput());

        return $versionResultEntity;
    }

    abstract public function setBinaryPath(string $path): void;
}
