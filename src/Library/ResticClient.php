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

use MuckiRestic\BackupClient;
use Symfony\Component\Process\Process;

class ResticClient extends BackupClient
{
    public function getProcess(string $command): Process
    {
        return Process::fromShellCommandline($command, null, null, null, -1);
    }
    public function getResticVersion(): string
    {
        $process = $this->getProcess(__DIR__.'../../bin/restic_0.17.3_linux_386 version');
        $process->run();

        return 'version';
    }
}