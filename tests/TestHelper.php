<?php

namespace MuckiRestic\Test;
class TestHelper
{
    public static function createDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return mkdir($path, 0777, true);
        }
        return false;
    }

    public static function createTextFiles(string $directory, array $files): bool
    {
        self::createDirectory($directory);
        foreach ($files as $index => $content) {
            $filePath = $directory.DIRECTORY_SEPARATOR.'file'.($index + 1).'.txt';
            if (file_put_contents($filePath, $content) === false) {
                return false;
            }
        }

        return true;
    }
}