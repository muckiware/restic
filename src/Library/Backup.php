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

use MuckiRestic\Library\Configuration;

class Backup extends Configuration
{
    /**
     * @throws \Exception
     */
    public function createRepository(): string
    {
        if($this->checkInputParametersByCommand('Init')) {

            $process = $this->getProcess(
                sprintf('export RESTIC_PASSWORD="%s"'."\n".'%s init --repo %s',
                    $this->repositoryPassword,
                    $this->resticBinaryPath,
                    $this->repositoryPath
                )
            );
            $process->run();

            return $process->getOutput();
        }

        throw new \Exception('Invalid configuration');
    }

    public function createBackup(): string
    {
        if($this->checkInputParametersByCommand('Backup')) {

            $process = $this->getProcess(
                sprintf('export RESTIC_PASSWORD="%s"'."\n".'export RESTIC_REPOSITORY="%s"'."\n".'%s backup %s',
                    $this->repositoryPassword,
                    $this->repositoryPath,
                    $this->resticBinaryPath,
                    $this->repositoryPath
                )
            );
            $process->run();

            return $process->getOutput();
        }

        throw new \Exception('Invalid configuration');
    }
}