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
namespace MuckiRestic\Test\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use MuckiRestic\Service\Helper;

class HelperTest extends TestCase
{
    private string $sandbox;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir().'/muckirestic_'.bin2hex(random_bytes(6));
        mkdir($this->sandbox, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->sandbox);
    }

    /**
     * Symlink-sicheres Aufräumen, unabhängig vom Code under Test.
     */
    private function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
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

    public function testDeletesPlainTreeCompletely(): void
    {
        $target = $this->sandbox.'/repo';
        mkdir($target.'/nested', 0777, true);
        file_put_contents($target.'/a.txt', 'a');
        file_put_contents($target.'/nested/b.txt', 'b');

        $this::assertTrue(Helper::deleteDirectory($target));
        $this::assertDirectoryDoesNotExist($target);
    }

    public function testDoesNotFollowSymlinkToDirectory(): void
    {
        $victim = $this->sandbox.'/victim';
        mkdir($victim, 0777, true);
        file_put_contents($victim.'/keep.txt', 'keep');

        $target = $this->sandbox.'/repo';
        mkdir($target, 0777, true);
        symlink($victim, $target.'/link');

        $this::assertTrue(Helper::deleteDirectory($target));
        $this::assertDirectoryDoesNotExist($target);
        $this::assertDirectoryExists($victim);
        $this::assertFileExists($victim.'/keep.txt');
    }

    public function testDoesNotFollowSymlinkToFile(): void
    {
        $victim = $this->sandbox.'/victim';
        mkdir($victim, 0777, true);
        file_put_contents($victim.'/keep.txt', 'keep');

        $target = $this->sandbox.'/repo';
        mkdir($target, 0777, true);
        symlink($victim.'/keep.txt', $target.'/link.txt');

        $this::assertTrue(Helper::deleteDirectory($target));
        $this::assertDirectoryDoesNotExist($target);
        $this::assertFileExists($victim.'/keep.txt');
    }

    public function testDoesNotFollowNestedSymlink(): void
    {
        $victim = $this->sandbox.'/victim';
        mkdir($victim, 0777, true);
        file_put_contents($victim.'/keep.txt', 'keep');

        $target = $this->sandbox.'/repo';
        mkdir($target.'/deep/deeper', 0777, true);
        symlink($victim, $target.'/deep/deeper/link');

        $this::assertTrue(Helper::deleteDirectory($target));
        $this::assertDirectoryDoesNotExist($target);
        $this::assertDirectoryExists($victim);
        $this::assertFileExists($victim.'/keep.txt');
    }

    /**
     * Every spelling that reduces to the symlink itself must be refused, no
     * matter how the caller writes the trailing separators or "." segments.
     *
     * @return array<string, array{string}>
     */
    public static function symlinkSpellingProvider(): array
    {
        return [
            'plain' => [''],
            'trailing slash' => ['/'],
            'double trailing slash' => ['//'],
            'trailing dot segment' => ['/.'],
            'trailing dot segment with slash' => ['/./'],
            'repeated dot segments' => ['/.//.'],
        ];
    }

    #[DataProvider('symlinkSpellingProvider')]
    public function testReturnsFalseWhenDirIsItselfASymlink(string $suffix): void
    {
        $victim = $this->sandbox.'/victim';
        mkdir($victim.'/subdir', 0777, true);
        file_put_contents($victim.'/keep.txt', 'keep');

        $link = $this->sandbox.'/link';
        symlink($victim, $link);

        $thrown = null;
        $result = null;

        try {
            $result = Helper::deleteDirectory($link.$suffix);
        } catch (\Throwable $throwable) {
            $thrown = $throwable;
        }

        $this::assertFileExists(
            $victim.'/keep.txt',
            sprintf('Symlink target file was deleted for spelling "link%s"', $suffix)
        );
        $this::assertDirectoryExists(
            $victim.'/subdir',
            sprintf('Symlink target subdirectory was deleted for spelling "link%s"', $suffix)
        );
        $this::assertNull(
            $thrown,
            sprintf(
                'deleteDirectory() must not throw for spelling "link%s", got %s: %s',
                $suffix,
                $thrown === null ? 'nothing' : $thrown::class,
                $thrown?->getMessage() ?? ''
            )
        );
        $this::assertFalse(
            $result,
            sprintf('deleteDirectory() must return false for spelling "link%s"', $suffix)
        );
    }

    public function testReturnsFalseWhenFinalComponentIsParentDirectory(): void
    {
        $target = $this->sandbox.'/repo';
        mkdir($target.'/nested', 0777, true);
        file_put_contents($target.'/a.txt', 'a');

        $thrown = null;
        $result = null;

        try {
            $result = Helper::deleteDirectory($target.'/nested/..');
        } catch (\Throwable $throwable) {
            $thrown = $throwable;
        }

        $this::assertFileExists($target.'/a.txt', 'Parent directory contents were deleted');
        $this::assertDirectoryExists($target.'/nested', 'Parent directory contents were deleted');
        $this::assertNull(
            $thrown,
            sprintf(
                'deleteDirectory() must not throw for a ".." spelling, got %s: %s',
                $thrown === null ? 'nothing' : $thrown::class,
                $thrown?->getMessage() ?? ''
            )
        );
        $this::assertFalse($result, 'deleteDirectory() must return false for a ".." spelling');
    }

    public function testReturnsFalseWhenDirectoryDoesNotExist(): void
    {
        $this::assertFalse(Helper::deleteDirectory($this->sandbox.'/does-not-exist'));
    }
}
