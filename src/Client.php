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

    public function getResticVersion(): ResultEntity
    {
        $process = $this->getProcess($this->resticBinaryPath.' version --json');
        $process->run();

        $mapper = new JsonMapper();
        $version = $mapper->map(json_decode($process->getOutput()),new Version());

        $versionResultEntity = new ResultEntity();
        $versionResultEntity->setCommandLine($process->getCommandLine());
        $versionResultEntity->setResticResponse($version);
        $versionResultEntity->setOutput($process->getOutput());

        return $versionResultEntity;
    }

    abstract public function setBinaryPath(string $path): void;
}
