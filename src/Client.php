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

    public function getResticVersion(): string
    {
        $process = $this->getProcess($this->resticBinaryPath.' version');
        $process->run();

        return $process->getOutput();
    }

    abstract public function setBinaryPath(string $path): void;
}
