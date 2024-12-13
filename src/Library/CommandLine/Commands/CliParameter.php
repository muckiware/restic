<?php

namespace MuckiRestic\Library\CommandLine\Commands;

class CliParameter
{
    protected array $parameters = [];

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function addParameter(string $parameter): void
    {
        $this->parameters[] = $parameter;
    }
}