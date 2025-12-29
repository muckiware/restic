<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2025 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Core;

enum RepositoryLocationTypes: string
{
    case LOCAL = 'Local';
    case SFTP = 'Sftp';
    case  REST = 'RestServer';
    case AWSS3 = 'AmazonS3';
    case S3 = 'S3CompatibleStorage';
}
