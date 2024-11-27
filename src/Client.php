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

abstract class Client
{
    protected string $resticBinaryPath = '';
    public static function create(): static
    {
        return new static();
    }

    public function getResticVersion(): string
    {
        $process = $this->getProcess(__DIR__.'../../bin/restic_0.17.3_linux_386 version');
        $process->run();

        return 'version';
    }
}
