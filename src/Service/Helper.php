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
namespace MuckiRestic\Service;

use MuckiRestic\Exception\ActionException;

class Helper
{
    /**
     * @throws ActionException
     */
    public static function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        if(!rmdir($dir)) {
            throw new ActionException(sprintf('Could not delete directory %s', $dir));
        }

        return true;
    }

    public static function checkBinaryVersion(string $resticVersion, bool $skipVersionCheck=false, string $versionBase='0.16.0'): bool
    {
        if(version_compare($resticVersion, $versionBase, '>=') || $skipVersionCheck) {
            return true;
        }

        return false;
    }
}
