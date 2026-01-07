<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2026 by Muckiware
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
    case BACKUP_AMAZON_S3 = 'BackupAmazonS3';
    case RESTORE = 'Restore';
    case RESTORE_AMAZON_S3 = 'RestoreAmazonS3';
    case FORGET = 'Forget';
    case FORGET_AMAZON_S3 = 'ForgetAmazonS3';
    case PRUNE = 'Prune';
    case PRUNE_AMAZON_S3 = 'PruneAmazonS3';
    case LIST = 'List';
    case SNAPSHOTS = 'Snapshots';
    case SNAPSHOTS_AMAZON_S3 = 'SnapshotsAmazonS3';
    case CHECK = 'Check';
    case CHECK_AMAZON_S3 = 'CheckAmazonS3';
    case VERSION = 'Version';
    case HELP = 'Help';
    case TEST_COMMAND = 'TestCommand';
    case UNLOCK = 'Unlock';
    case UNLOCK_AMAZON_S3 = 'UnlockAmazonS3';
    case SINGLE_FORGET = 'SingleForget';
    case SINGLE_FORGET_AMAZON_S3 = 'SingleForgetAmazonS3';
    case STATS = 'Stats';
    case STATS_AMAZON_S3 = 'StatsAmazonS3';
}
