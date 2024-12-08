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

class SnapshotsResultEntity
{
    protected ?string $old = null;
    protected ?string $new = null;

    public function getOld(): ?string
    {
        return $this->old;
    }

    public function setOld(string $old): void
    {
        $this->old = $old;
    }

    public function getNew(): ?string
    {
        return $this->new;
    }

    public function setNew(string $new): void
    {
        $this->new = $new;
    }
}
