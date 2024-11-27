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

abstract class BackupClient
{
    protected string $resticBinaryPath = '';
    public static function create(): static
    {
        return new static();
    }

    public function setResticBinaryPath(string $resticBinaryPath = ''): self
    {
        if ($resticBinaryPath !== '' && ! str_ends_with($resticBinaryPath, '/')) {
            $resticBinaryPath .= '/';
        }

        $this->resticBinaryPath = $resticBinaryPath;

        return $this;
    }

    abstract public function getResticVersion(): string;
}
