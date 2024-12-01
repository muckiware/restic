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
namespace MuckiRestic\Entity\Result;

use MuckiRestic\Entity\DataSet;

class CheckResultEntity extends DataSet
{
    /**
     * @var array <mixed>
     */
    protected array $processed;

    /**
     * @return array <mixed>
     */
    public function getProcessed(): array
    {
        return $this->processed;
    }

    /**
     * @param array <mixed> $processed
     */
    public function setProcessed(array $processed): void
    {
        $this->processed = $processed;
    }

    public function addProcessed(string $processed): void
    {
        $this->processed[] = $processed;
    }
}
