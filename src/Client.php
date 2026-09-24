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
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResticResponse\Version;
use MuckiRestic\ResultParser\VersionResultParser;
use MuckiRestic\ResultParser\OutputParser;

abstract class Client
{
    protected string $resticBinaryPath = '';

    /**
     * Wall clock limit for a restic process, in seconds. Null removes the limit.
     */
    protected ?float $processTimeout = 3600.0;

    /**
     * Abort a restic process after this many seconds without output. Null disables it.
     */
    protected ?float $processIdleTimeout = null;

    final public function __construct()
    {}
    public static function create(): static
    {
        return new static();
    }

    /**
     * @param list<string> $command
     * @param array<string,string|null> $envParameters
     * @return Process
     */
    public function getProcess(array $command, array $envParameters = []): Process
    {
        $process = new Process($command, null, $envParameters, null, $this->processTimeout);

        if ($this->processIdleTimeout !== null) {
            $process->setIdleTimeout($this->processIdleTimeout);
        }

        return $process;
    }

    /**
     * @param list<string> $arguments
     * @return Process
     */
    public function requestVersion(array $arguments): Process
    {
        $this->assertBinaryIsExecutable();

        $process = $this->getProcess(array_merge([$this->resticBinaryPath], $arguments));
        $process->run();

        return $process;
    }

    /**
     * Fail before a process is started, so that a wrong path is reported as the
     * configuration error it is instead of surfacing as exit code 127 with no output.
     *
     * @throws InvalidConfigurationException
     */
    protected function assertBinaryIsExecutable(): void
    {
        if ($this->resolveBinaryPath($this->resticBinaryPath) === null) {
            throw InvalidConfigurationException::binaryNotFound($this->resticBinaryPath);
        }
    }

    /**
     * A value carrying a separator is taken as given, relative to the working
     * directory; a bare name is looked up through PATH, which is how the default
     * 'restic' is meant to resolve.
     */
    private function resolveBinaryPath(string $binary): ?string
    {
        if ($binary === '') {
            return null;
        }

        if (str_contains($binary, '/') || str_contains($binary, DIRECTORY_SEPARATOR)) {
            return is_file($binary) && is_executable($binary) ? $binary : null;
        }

        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $directory) {
            if ($directory === '') {
                continue;
            }

            $candidate = $directory.DIRECTORY_SEPARATOR.$binary;
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
    public function getResticVersion(): ResultEntity
    {
        $process = $this->requestVersion(['version']);
        $version = $this->createVersion($process->getOutput());
        if (version_compare($version->getVersion(), '0.17.0', '>=')) {

            $versionRawResult = OutputParser::fixJsonOutput(
                $this->requestVersion(['version', '--json'])->getOutput()
            );
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
            throw InvalidConfigurationException::unreadableVersion($this->resticBinaryPath, $processOutput);
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
