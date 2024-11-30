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

class FilesDirsResultEntity extends DataSet
{
    protected int $new;
    protected int $changed;
    protected int $unmodified;

    public function getNew(): int
    {
        return $this->new;
    }

    public function setNew(int $new): void
    {
        $this->new = $new;
    }

    public function getChanged(): int
    {
        return $this->changed;
    }

    public function setChanged(int $changed): void
    {
        $this->changed = $changed;
    }

    public function getUnmodified(): int
    {
        return $this->unmodified;
    }

    public function setUnmodified(int $unmodified): void
    {
        $this->unmodified = $unmodified;
    }
}
