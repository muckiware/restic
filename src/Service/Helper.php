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
        $dir = self::reduceTrailingNoOpSegments($dir);
        if ($dir === '' || self::hasParentDirectoryAsFinalComponent($dir)) {
            return false;
        }

        if (!is_dir($dir) || is_link($dir)) {
            return false;
        }

        $base = realpath($dir);
        if ($base === false) {
            return false;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $path = $item->getPathname();
            self::assertWithin($base, $path);

            if ($item->isLink() || !$item->isDir()) {
                unlink($path);
                continue;
            }
            rmdir($path);
        }

        if (!rmdir($dir)) {
            throw new ActionException(sprintf('Could not delete directory %s', $dir));
        }

        return true;
    }

    /**
     * First barrier: is_link() resolves a path whose final component is a separator
     * or a "." segment instead of stat-ing the link itself, so "link/", "link/." and
     * "link/.//." would all slip past the symlink guard. Strip trailing separators and
     * trailing "." segments repeatedly until neither remains, so every spelling that
     * names the same directory entry is reduced to that entry before it is checked.
     */
    private static function reduceTrailingNoOpSegments(string $dir): string
    {
        while (true) {
            $trimmed = rtrim($dir, '/\\');

            if ($trimmed !== '' && (str_ends_with($trimmed, '/.') || str_ends_with($trimmed, '\\.'))) {
                $dir = substr($trimmed, 0, -1);
                continue;
            }

            return $trimmed === '.' ? '' : $trimmed;
        }
    }

    /**
     * A path ending in ".." names a directory other than the one the caller nominally
     * supplied. Deleting it is never the intent of createRepository(overwrite: true),
     * so such spellings are refused rather than resolved.
     */
    private static function hasParentDirectoryAsFinalComponent(string $dir): bool
    {
        $components = preg_split('#[/\\\\]#', $dir);

        return $components !== false && end($components) === '..';
    }

    /**
     * Second barrier: every deleted path must sit below the resolved base directory.
     *
     * @throws ActionException
     */
    private static function assertWithin(string $base, string $path): void
    {
        $parent = realpath(dirname($path));
        if ($parent === false
            || ($parent !== $base && !str_starts_with($parent, $base.DIRECTORY_SEPARATOR))
        ) {
            throw new ActionException(sprintf(
                'Refusing to delete path outside of %s: %s',
                $base,
                $path
            ));
        }
    }

    public static function checkBinaryVersion(string $resticVersion, bool $skipVersionCheck=false, string $versionBase='0.16.0'): bool
    {
        if(version_compare($resticVersion, $versionBase, '>=') || $skipVersionCheck) {
            return true;
        }

        return false;
    }
}
