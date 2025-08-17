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
namespace MuckiRestic\Core;

class Defaults
{
    const DEFAULT_COMMAND_PARAMETER_CONFIGURATION = 'commandParameterConfiguration.json';
    const RESTIC_VERSION_TEXT_PATTERN = '/\b\d+\.\d+\.\d+\b/';
    const GO_VERSION_TEXT_PATTERN = '/go(\d+\.\d+\.\d+)/';
    const MAXIMUM_RESTIC_PARAMETER_LENGTH = 255;
}