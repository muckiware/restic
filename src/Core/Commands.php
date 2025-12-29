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

enum Commands: string
{
    case INIT = 'Init';
    case INIT_AMAZON_S3 = 'InitAmazonS3';
    case BACKUP = 'Backup';
    case RESTORE = 'Restore';
    case FORGET = 'Forget';
    case PRUNE = 'Prune';
    case LIST = 'List';
    case SNAPSHOTS = 'Snapshots';
    case CHECK = 'Check';
    case VERSION = 'Version';
    case HELP = 'Help';
    case TEST_COMMAND = 'TestCommand';
    case UNLOCK = 'Unlock';
    case SINGLE_FORGET = 'SingleForget';
}
