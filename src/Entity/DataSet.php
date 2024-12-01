<?php declare(strict_types=1);
/**
 * LightsOnSSO plugin
 *
 * @package    LightsOnSSO
 * @copyright  Copyright (c) 2024 by LightsOn GmbH
 * @author     LightsOn GmbH
 *
 */
namespace MuckiRestic\Entity;

class DataSet
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
