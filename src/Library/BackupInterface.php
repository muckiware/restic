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

use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;

interface BackupInterface
{
    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createRepository(bool $overwrite=false): ResultEntity;

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function createBackup(): ResultEntity;

    /**
     * @throws InvalidConfigurationException
     */
    public function prepareBackup(): void;

    /**
     * @throws InvalidConfigurationException
     * @throws \Exception
     */
    public function checkBackup(): ResultEntity;

    /**
     * @throws InvalidConfigurationException
     */
    public function runUnlockCommand(): void;

    /**
     * @throws InvalidConfigurationException
     */
    public function runPruneCommand(): void;
}
