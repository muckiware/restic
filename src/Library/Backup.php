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

use MuckiRestic\Client;
use Symfony\Component\Process\Process;

class Backup extends Client
{
    public function getProcess(string $command): Process
    {
        return Process::fromShellCommandline($command, null, null, null, 1000);
    }
    public function getResticVersion(): string
    {
        $process = $this->getProcess('/var/www/html/bin/restic_0.17.3_linux_386 version');
        $process->run();

        return $process->getOutput();
    }
}