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
namespace MuckiRestic\Entity;

use MuckiRestic\Entity\ParameterEntity;

class CommandEntity extends DataSet
{

    protected string $command;
    protected string $description;

    /**
     * @var array<ParameterEntity>
     */
    protected array $parameters;

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array<ParameterEntity>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @var array<ParameterEntity>
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function addParameter(ParameterEntity $parameter): void
    {
        $this->parameters[] = $parameter;
    }
}
