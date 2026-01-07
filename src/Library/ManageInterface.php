<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Library;

use Symfony\Component\Process\Process;

use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Entity\Result\ResultEntity;

interface ManageInterface
{
    public function getSnapshots(): ResultEntity;
    public function removeSnapshots(): ResultEntity;
    public function removeSnapshotById(): ResultEntity;
    public function executePrune(): ResultEntity;
    public function getRepositoryStats(): ResultEntity;

    public function resultsExecution(Process $process, bool $isJsonOutput, bool $skipVersionCheck=false): ResultEntity;
}
