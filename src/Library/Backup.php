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
                'export RESTIC_PASSWORD="Y73wHU5Dw96WWV"'."\n".$this->resticBinaryPath.' init --repo '.$this->repositoryPath
            );
            $process->run();

            return $process->getOutput();
        }

        throw new \Exception('Invalid configuration');
    }
}