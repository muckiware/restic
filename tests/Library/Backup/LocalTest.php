<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Test\Library\Backup;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Exception\ActionException;
use MuckiRestic\Library\Backup\Local;

class LocalTest extends TestCase
{
    private string $sandbox;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir().'/muckirestic_local_'.bin2hex(random_bytes(6));
        mkdir($this->sandbox, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->sandbox);
    }

    private function removeTree(string $dir): void
    {
        if (!is_dir($dir) && !is_link($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $path = $item->getPathname();
            if ($item->isLink() || !$item->isDir()) {
                unlink($path);
                continue;
            }
            rmdir($path);
        }

        rmdir($dir);
    }

    public function testCreateRepositoryThrowsActionExceptionWhenRepositoryPathIsASymlink(): void
    {
        $victim = $this->sandbox.'/victim';
        mkdir($victim, 0777, true);

        $link = $this->sandbox.'/link';
        symlink($victim, $link);

        $local = Local::create();
        $local->setRepositoryPassword('secret');
        $local->setRepositoryPath($link);

        $this->expectException(ActionException::class);
        $this->expectExceptionMessage($link);

        $local->createRepository(true);
    }
}
