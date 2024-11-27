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

class Configuration extends Client
{
    protected string $resticBinaryPath = '';
    public function setResticBinaryPath(string $resticBinaryPath = ''): self
    {
        if ($resticBinaryPath !== '' && ! str_ends_with($resticBinaryPath, '/')) {
            $resticBinaryPath .= '/';
        }

        $this->resticBinaryPath = $resticBinaryPath;

        return $this;
    }

    public function getResticBinaryPath(): string
    {
        return $this->resticBinaryPath;
    }
}
