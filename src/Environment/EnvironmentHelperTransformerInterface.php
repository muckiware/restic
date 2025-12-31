<?php declare(strict_types=1);

namespace MuckiRestic\Environment;

interface EnvironmentHelperTransformerInterface
{
    public static function transform(EnvironmentHelperTransformerData $data): void;
}
