# Command Injection Fix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Shell-Injection (F-01) und Symlink-Traversal (S-2) in `muckiware/restic` strukturell beseitigen und als 1.5.0 vorbereiten.

**Architecture:** Die Kommandoklassen liefern statt eines Shell-Strings eine Argumentliste `list<string>`. `Client::getProcess()` führt sie mit `new Process()` über `execvp()` aus, ohne `/bin/sh`. Argument Injection wird über `--flag=wert`-Einzeltokens, `--` vor Positionsargumenten und eine Abweisung von Werten mit führendem `-` in den Settern abgedeckt. `Helper::deleteDirectory()` entfernt Symlinks als Link, statt ihr Ziel aufzulösen.

**Tech Stack:** PHP 8.1+, symfony/process, PHPUnit 10, PHPStan Level 8, DDEV (PHP 8.4)

**Spec:** `docs/superpowers/specs/2026-09-17-command-injection-fix-design.md`

## Global Constraints

- **Es wird nichts committet.** Entscheidung des Maintainers. Alle Änderungen bleiben uncommittet im Working Tree. Die üblichen Commit-Schritte sind durch Verifikationsschritte ersetzt.
- **Alle Kommandos laufen in DDEV.** Das Host-PHP ist ein defektes homebrew 7.4, und `vendor/` hat einen `>= 8.4.1`-Platform-Check. Präfix immer `ddev exec`.
- **Testkommando Unit:** `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml`
- **Testkommando vollständig:** `ddev exec ./vendor/bin/phpunit --configuration=phpunit.xml`
- **Statische Analyse:** `ddev exec ./vendor/bin/phpstan analyse -n --no-progress`
- **Erwarteter Endzustand:** `phpunit.xml` meldet 4 Fehler von 36+ Tests — ausschließlich die vier `IntegrationAwsS3Test`-Fälle mit `Missing required parameter awsS3Endpoint`, weil `.env.test` lokal leer ist. Jeder andere Fehler ist eine Regression.
- **PHPStan muss am Ende fehlerfrei sein.** Aktuell zwei vorbestehende Fehler in `src/Library/Backup/AmazonS3.php:172` und `:183`; Task 4 behebt sie.
- **Zielversion:** `1.5.0` in `composer.json`.
- **Namespace:** `MuckiRestic\` → `src/`, `MuckiRestic\Test\` → `tests/`
- **Codestil:** `declare(strict_types=1);` in jeder neuen Datei, PSR-12, Copyright-Header wie in den Nachbardateien.

---

## File Structure

| Datei | Verantwortung | Task |
|---|---|---|
| `tests/Service/HelperTest.php` | neu — Symlink-Verhalten von `deleteDirectory()` | 1 |
| `src/Service/Helper.php` | ändern — symlink-sicheres Löschen | 1 |
| `tests/Library/ConfigurationValidationTest.php` | neu — Dash-Abweisung in 13 Settern | 2 |
| `src/Exception/InvalidConfigurationException.php` | ändern — benannter Konstruktor | 2 |
| `src/Library/Configuration.php` | ändern — Validierung, später Array-Signaturen | 2, 3 |
| `tests/Library/CommandLine/CommandBuilderTest.php` | neu — Zielform aller 21 Kommandos | 3 |
| `src/Library/CommandLine/CommandLineInterface.php` | ändern — Rückgabetyp `array` | 3 |
| `src/Library/CommandLine/CommandLineFactory.php` | ändern — Rückgabetyp `array` + Typprüfung | 3 |
| `src/Client.php` | ändern — `new Process()` statt `fromShellCommandline()` | 3 |
| `src/Library/CommandLine/Commands/*.php` (21) | ändern — Argumentlisten | 3 |
| `tests/Library/CommandLine/Commands/ForgetTest.php` | ändern — Array statt String | 3 |
| `src/Library/Backup/AmazonS3.php` | ändern — Null-Guard Bucket-Name | 4 |
| `composer.json`, `CHANGELOG.md`, `README.md` | ändern/neu — Release-Doku | 5 |

Task 3 ist bewusst nicht weiter aufgeteilt. Der Wechsel des Rückgabetyps in `CommandLineInterface` bricht durch das Signatur-Matching von PHP sofort alle 21 implementierenden Klassen — ein Zwischenzustand mit grüner Suite existiert nicht. Ein Reviewer kann die Teile nicht einzeln annehmen oder ablehnen.

---

### Task 1: Helper::deleteDirectory() gegen Symlinks absichern (S-2)

**Files:**
- Create: `tests/Service/HelperTest.php`
- Modify: `src/Service/Helper.php:19-43`

**Interfaces:**
- Consumes: nichts aus vorherigen Tasks
- Produces: `Helper::deleteDirectory(string $dir): bool` — Signatur unverändert. Neues Verhalten: Symlinks werden als Link entfernt, `false` bei nicht existierendem Verzeichnis oder wenn `$dir` selbst ein Symlink ist, `ActionException` wenn ein Pfad außerhalb von `realpath($dir)` gelöscht werden müsste.

- [ ] **Step 1: Failing Test schreiben**

Create `tests/Service/HelperTest.php`:

```php
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

    public function testReturnsFalseWhenDirIsItselfASymlink(): void
    {
        $victim = $this->sandbox.'/victim';
        mkdir($victim, 0777, true);
        file_put_contents($victim.'/keep.txt', 'keep');

        $link = $this->sandbox.'/link';
        symlink($victim, $link);

        $this::assertFalse(Helper::deleteDirectory($link));
        $this::assertFileExists($victim.'/keep.txt');
    }

    public function testReturnsFalseWhenDirectoryDoesNotExist(): void
    {
        $this::assertFalse(Helper::deleteDirectory($this->sandbox.'/does-not-exist'));
    }
}
```

- [ ] **Step 2: Test laufen lassen und Fehlschlag bestätigen**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml tests/Service/HelperTest.php`

Expected: FAIL. `testDoesNotFollowSymlinkToDirectory`, `testDoesNotFollowSymlinkToFile` und `testDoesNotFollowNestedSymlink` scheitern, weil `assertDirectoryExists($victim)` bzw. `assertFileExists` fehlschlägt — der heutige Code löscht das Symlink-Ziel. Zusätzlich wirft `deleteDirectory()` eine `ActionException` („Could not delete directory"), weil der zurückgebliebene Symlink den abschließenden `rmdir()` blockiert. `testReturnsFalseWhenDirIsItselfASymlink` scheitert ebenfalls.

- [ ] **Step 3: Implementierung**

Modify `src/Service/Helper.php` — `deleteDirectory()` vollständig ersetzen und `assertWithin()` ergänzen:

```php
    /**
     * @throws ActionException
     */
    public static function deleteDirectory(string $dir): bool
    {
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
```

Drei Punkte, die nicht verändert werden dürfen:
1. `getPathname()` statt `getRealPath()` — sonst trifft `unlink()` das Symlink-Ziel.
2. `isLink()` **vor** `isDir()` — ein Symlink auf ein Verzeichnis meldet `isDir() === true`.
3. `is_link($dir)` in der Eingangsprüfung — sonst löscht ein als `$dir` übergebener Symlink sein Ziel aus.

- [ ] **Step 4: Test laufen lassen und Erfolg bestätigen**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml tests/Service/HelperTest.php`

Expected: PASS, 6 Tests.

- [ ] **Step 5: Keine Regression in der übrigen Suite**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml`

Expected: PASS, 29 Tests (23 bestehende + 6 neue).

---

### Task 2: Werte mit führendem Bindestrich abweisen

**Files:**
- Create: `tests/Library/ConfigurationValidationTest.php`
- Modify: `src/Exception/InvalidConfigurationException.php`
- Modify: `src/Library/Configuration.php` (13 Setter + neue private Methode)

**Interfaces:**
- Consumes: nichts aus Task 1
- Produces:
  - `InvalidConfigurationException::optionLikeValue(string $parameter, string $value): self`
  - `Configuration::assertNotOptionLike(string $parameter, ?string $value): void` — `private`, wird in Task 3 nicht erweitert
  - 13 Setter werfen bei führendem `-` eine `InvalidConfigurationException`

- [ ] **Step 1: Failing Test schreiben**

Create `tests/Library/ConfigurationValidationTest.php`:

```php
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
namespace MuckiRestic\Test\Library;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use MuckiRestic\Library\Backup;
use MuckiRestic\Library\Configuration;
use MuckiRestic\Exception\InvalidConfigurationException;

class ConfigurationValidationTest extends TestCase
{
    /**
     * @return array<string, array{0: callable(Configuration, string): void}>
     */
    public static function optionLikeSetterProvider(): array
    {
        return [
            'setBinaryPath'     => [static fn (Configuration $c, string $v) => $c->setBinaryPath($v)],
            'setRepositoryPath' => [static fn (Configuration $c, string $v) => $c->setRepositoryPath($v)],
            'setBackupPath'     => [static fn (Configuration $c, string $v) => $c->setBackupPath($v)],
            'setRestoreTarget'  => [static fn (Configuration $c, string $v) => $c->setRestoreTarget($v)],
            'setRestoreItem'    => [static fn (Configuration $c, string $v) => $c->setRestoreItem($v)],
            'setSnapshotId'     => [static fn (Configuration $c, string $v) => $c->setSnapshotId($v)],
            'addSnapshotId'     => [static fn (Configuration $c, string $v) => $c->addSnapshotId($v)],
            'setSnapshotIds'    => [static fn (Configuration $c, string $v) => $c->setSnapshotIds([$v])],
            'setHostName'       => [static fn (Configuration $c, string $v) => $c->setHostName($v)],
            'setGroupBy'        => [static fn (Configuration $c, string $v) => $c->setGroupBy($v)],
            'setTag'            => [static fn (Configuration $c, string $v) => $c->setTag($v)],
            'setTags'           => [static fn (Configuration $c, string $v) => $c->setTags([$v])],
            'setAwsS3Endpoint'  => [static fn (Configuration $c, string $v) => $c->setAwsS3Endpoint($v)],
        ];
    }

    /**
     * @param callable(Configuration, string): void $setter
     */
    #[DataProvider('optionLikeSetterProvider')]
    public function testRejectsValueStartingWithDash(callable $setter): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $setter(Backup::create(), '--exclude=/etc');
    }

    /**
     * @param callable(Configuration, string): void $setter
     */
    #[DataProvider('optionLikeSetterProvider')]
    public function testAcceptsOrdinaryValue(callable $setter): void
    {
        $setter(Backup::create(), 'plain-value');
        $this::assertTrue(true, 'setter accepted an ordinary value');
    }

    public function testSetSnapshotIdAcceptsNull(): void
    {
        $configuration = Backup::create();
        $configuration->setSnapshotId(null);
        $this::assertNull($configuration->getSnapshotId());
    }

    public function testSetAwsS3EndpointAcceptsNull(): void
    {
        $configuration = Backup::create();
        $configuration->setAwsS3Endpoint(null);
        $this::assertNull($configuration->getAwsS3Endpoint());
    }
}
```

- [ ] **Step 2: Test laufen lassen und Fehlschlag bestätigen**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml tests/Library/ConfigurationValidationTest.php`

Expected: FAIL. Alle 13 `testRejectsValueStartingWithDash`-Fälle scheitern mit „Failed asserting that exception of type InvalidConfigurationException is thrown".

- [ ] **Step 3: Benannten Konstruktor ergänzen**

Modify `src/Exception/InvalidConfigurationException.php` — Methode in die Klasse einfügen:

```php
    public static function optionLikeValue(string $parameter, string $value): self
    {
        return new self(sprintf(
            'Parameter %s must not start with a dash, got "%s"',
            $parameter,
            $value
        ));
    }
```

- [ ] **Step 4: Validierung in Configuration ergänzen**

Modify `src/Library/Configuration.php`. Zuerst den Import ergänzen (`InvalidConfigurationException` ist bereits importiert) und die private Methode am Ende der Klasse einfügen:

```php
    /**
     * Rejects values that restic would read as an option instead of a value.
     *
     * @throws InvalidConfigurationException
     */
    private function assertNotOptionLike(string $parameter, ?string $value): void
    {
        if ($value !== null && str_starts_with($value, '-')) {
            throw InvalidConfigurationException::optionLikeValue($parameter, $value);
        }
    }
```

Dann die 13 Setter. Jeweils die erste Zeile des Methodenrumpfs ergänzen:

```php
    public function setBinaryPath(string $path): void
    {
        $this->assertNotOptionLike('binaryPath', $path);
        $this->resticBinaryPath = $path;
    }

    public function setRepositoryPath(string $path): void
    {
        $this->assertNotOptionLike('repositoryPath', $path);
        $this->repositoryPath = $path;
    }

    public function setBackupPath(string $backupPath): void
    {
        $this->assertNotOptionLike('backupPath', $backupPath);
        $this->backupPath = $backupPath;
    }

    public function setRestoreItem(string $restoreItem): void
    {
        $this->assertNotOptionLike('restoreItem', $restoreItem);
        $this->restoreItem = $restoreItem;
    }

    public function setRestoreTarget(string $restoreTarget): void
    {
        $this->assertNotOptionLike('restoreTarget', $restoreTarget);
        $this->restoreTarget = $restoreTarget;
    }

    public function setSnapshotId(?string $snapshotId): void
    {
        $this->assertNotOptionLike('snapshotId', $snapshotId);
        $this->snapshotId = $snapshotId;
    }

    /**
     * @param array<string> $snapshotIds
     */
    public function setSnapshotIds(array $snapshotIds): void
    {
        foreach ($snapshotIds as $snapshotId) {
            $this->assertNotOptionLike('snapshotIds', $snapshotId);
        }
        $this->snapshotIds = $snapshotIds;
    }

    public function addSnapshotId(string $snapshotId): void
    {
        $this->assertNotOptionLike('snapshotIds', $snapshotId);
        $this->snapshotIds[] = $snapshotId;
    }

    public function setHostName(string $hostName): void
    {
        $this->assertNotOptionLike('hostName', $hostName);
        $this->hostName = substr($hostName, 0, Defaults::MAXIMUM_RESTIC_PARAMETER_LENGTH);
    }

    public function setGroupBy(string $groupBy): void
    {
        $this->assertNotOptionLike('groupBy', $groupBy);
        $this->groupBy = substr($groupBy, 0, Defaults::MAXIMUM_RESTIC_PARAMETER_LENGTH);
    }

    public function setTag(string $tag): void
    {
        $this->assertNotOptionLike('tags', $tag);
        $this->tags[] = $tag;
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): void
    {
        foreach ($tags as $tag) {
            $this->assertNotOptionLike('tags', $tag);
        }
        $this->tags = $tags;
    }

    public function setAwsS3Endpoint(?string $awsS3Endpoint): void
    {
        $this->assertNotOptionLike('awsS3Endpoint', $awsS3Endpoint);
        $this->awsS3Endpoint = $awsS3Endpoint;
    }
```

Die übrigen Setter bleiben unangetastet. `repositoryPassword`, `awsAccessKeyId`, `awsSecretAccessKey` und `awsS3Region` gehen in die Prozessumgebung, `awsS3BucketName` ins AWS SDK, `sftpUsername`/`sftpPassword` werden derzeit nirgends verwendet — keiner dieser Werte landet in argv.

- [ ] **Step 5: Test laufen lassen und Erfolg bestätigen**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml tests/Library/ConfigurationValidationTest.php`

Expected: PASS, 28 Tests (13 + 13 + 2).

- [ ] **Step 6: Keine Regression in der übrigen Suite**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml`

Expected: PASS, 57 Tests. Sollte ein bestehender Test scheitern, weil er einen Wert mit führendem `-` setzt: das wäre ein echter Fund, nicht der Test ist anzupassen sondern zu melden.

---

### Task 3: Kommandozeilen auf Argumentlisten umstellen (F-01)

**Files:**
- Create: `tests/Library/CommandLine/CommandBuilderTest.php`
- Modify: `src/Library/CommandLine/CommandLineInterface.php`
- Modify: `src/Library/CommandLine/CommandLineFactory.php`
- Modify: `src/Client.php:33-58`
- Modify: `src/Library/Configuration.php` (`getCommandStringByCommand`, `createProcess`)
- Modify: alle 21 Dateien in `src/Library/CommandLine/Commands/` außer `CliParameter.php`
- Modify: `tests/Library/CommandLine/Commands/ForgetTest.php`

**Interfaces:**
- Consumes: `Configuration::assertNotOptionLike()` aus Task 2 (indirekt über die Setter)
- Produces:
  - `CommandLineInterface::getCommandLine(Configuration $c): array` — `list<string>`
  - `CommandLineFactory::createCommandLine(Configuration $c, Commands $cmd): array` — `list<string>`
  - `Client::getProcess(array $command, array $envParameters = []): Process`
  - `Client::requestVersion(array $arguments): Process`
  - `Configuration::getCommandArgumentsByCommand(Commands $command): array` — ersetzt `getCommandStringByCommand()`
  - `getEnvParameters()` bleibt unverändert

- [ ] **Step 1: Failing Test schreiben**

Create `tests/Library/CommandLine/CommandBuilderTest.php`:

```php
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
namespace MuckiRestic\Test\Library\CommandLine;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Backup as BackupClient;
use MuckiRestic\Library\Configuration;
use MuckiRestic\Library\CommandLine\Commands\Backup;
use MuckiRestic\Library\CommandLine\Commands\BackupAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Check;
use MuckiRestic\Library\CommandLine\Commands\CheckAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Forget;
use MuckiRestic\Library\CommandLine\Commands\ForgetAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Init;
use MuckiRestic\Library\CommandLine\Commands\InitAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\ListObjects;
use MuckiRestic\Library\CommandLine\Commands\Prune;
use MuckiRestic\Library\CommandLine\Commands\PruneAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Restore;
use MuckiRestic\Library\CommandLine\Commands\RestoreAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\SingleForget;
use MuckiRestic\Library\CommandLine\Commands\SingleForgetAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Snapshots;
use MuckiRestic\Library\CommandLine\Commands\SnapshotsAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Stats;
use MuckiRestic\Library\CommandLine\Commands\StatsAmazonS3;
use MuckiRestic\Library\CommandLine\Commands\Unlock;
use MuckiRestic\Library\CommandLine\Commands\UnlockAmazonS3;

class CommandBuilderTest extends TestCase
{
    private const BINARY   = '/usr/bin/restic';
    private const REPO     = '/srv/repo';
    private const ENDPOINT = 's3:https://s3.example.com/bucket';

    private function config(): Configuration
    {
        $configuration = BackupClient::create();
        $configuration->setBinaryPath(self::BINARY);
        $configuration->setRepositoryPassword('secret');
        $configuration->setRepositoryPath(self::REPO);
        $configuration->setBackupPath('/srv/data');
        $configuration->setRestoreTarget('/srv/restore');
        $configuration->setSnapshotId('abc12345');
        $configuration->setSnapshotIds(['abc12345', 'def67890']);
        $configuration->setHostName('shop01');
        $configuration->setGroupBy('host');
        $configuration->setTags(['nightly']);
        $configuration->setKeepDaily(7);
        $configuration->setKeepWeekly(5);
        $configuration->setKeepMonthly(12);
        $configuration->setKeepYearly(75);
        $configuration->setKeepLast(3);
        $configuration->setAwsS3Endpoint(self::ENDPOINT);

        return $configuration;
    }

    public function testInit(): void
    {
        $this::assertSame(
            [self::BINARY, 'init', '--repo='.self::REPO, '--json'],
            Init::getCommandLine($this->config())
        );
    }

    public function testInitAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'init', '--repo='.self::ENDPOINT, '--json'],
            InitAmazonS3::getCommandLine($this->config())
        );
    }

    public function testBackup(): void
    {
        $this::assertSame([
            self::BINARY,
            '--repo='.self::REPO,
            'backup',
            '--json',
            '--compression=auto',
            '--host=shop01',
            '--tag=nightly',
            '--',
            '/srv/data',
        ], Backup::getCommandLine($this->config()));
    }

    public function testBackupAmazonS3(): void
    {
        $this::assertSame([
            self::BINARY,
            '--repo='.self::ENDPOINT,
            'backup',
            '--json',
            '--compression=auto',
            '--host=shop01',
            '--tag=nightly',
            '--',
            '/srv/data',
        ], BackupAmazonS3::getCommandLine($this->config()));
    }

    public function testCheck(): void
    {
        $this::assertSame(
            [self::BINARY, 'check', '--read-data'],
            Check::getCommandLine($this->config())
        );
    }

    public function testCheckAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::ENDPOINT, 'check', '--read-data'],
            CheckAmazonS3::getCommandLine($this->config())
        );
    }

    public function testSnapshots(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::REPO, 'snapshots', '--json', '--host=shop01'],
            Snapshots::getCommandLine($this->config())
        );
    }

    public function testSnapshotsAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::ENDPOINT, 'snapshots', '--json', '--host=shop01'],
            SnapshotsAmazonS3::getCommandLine($this->config())
        );
    }

    public function testForget(): void
    {
        $this::assertSame([
            self::BINARY,
            'forget',
            '--repo='.self::REPO,
            '--prune',
            '--json',
            '--host=shop01',
            '--keep-daily=7',
            '--keep-weekly=5',
            '--keep-monthly=12',
            '--keep-yearly=75',
            '--keep-last=3',
            '--group-by=host',
            '--tag=nightly',
            '--',
            'abc12345',
            'def67890',
        ], Forget::getCommandLine($this->config()));
    }

    public function testForgetAmazonS3(): void
    {
        $this::assertSame([
            self::BINARY,
            'forget',
            '--repo='.self::ENDPOINT,
            '--prune',
            '--json',
            '--host=shop01',
            '--keep-daily=7',
            '--keep-weekly=5',
            '--keep-monthly=12',
            '--keep-yearly=75',
            '--keep-last=3',
            '--group-by=host',
            '--tag=nightly',
            '--',
            'abc12345',
            'def67890',
        ], ForgetAmazonS3::getCommandLine($this->config()));
    }

    public function testSingleForget(): void
    {
        $this::assertSame(
            [self::BINARY, 'forget', '--repo='.self::REPO, '--', 'abc12345'],
            SingleForget::getCommandLine($this->config())
        );
    }

    public function testSingleForgetAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'forget', '--repo='.self::ENDPOINT, '--', 'abc12345'],
            SingleForgetAmazonS3::getCommandLine($this->config())
        );
    }

    public function testRestore(): void
    {
        $this::assertSame([
            self::BINARY,
            'restore',
            '--repo='.self::REPO,
            '--target=/srv/restore',
            '--json',
            '--',
            'latest',
        ], Restore::getCommandLine($this->config()));
    }

    public function testRestoreAmazonS3(): void
    {
        $this::assertSame([
            self::BINARY,
            'restore',
            '--repo='.self::ENDPOINT,
            '--target=/srv/restore',
            '--json',
            '--',
            'latest',
        ], RestoreAmazonS3::getCommandLine($this->config()));
    }

    public function testPrune(): void
    {
        $this::assertSame([self::BINARY, 'prune'], Prune::getCommandLine($this->config()));
    }

    public function testPruneAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'prune', '--repo='.self::ENDPOINT],
            PruneAmazonS3::getCommandLine($this->config())
        );
    }

    public function testStats(): void
    {
        $this::assertSame(
            [self::BINARY, 'stats', '--json'],
            Stats::getCommandLine($this->config())
        );
    }

    /**
     * Also covers finding B-5: this class used getRepositoryPath() instead of
     * getAwsS3Endpoint(), unlike every other AmazonS3 command.
     */
    public function testStatsAmazonS3UsesTheEndpoint(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::ENDPOINT, 'stats', '--json'],
            StatsAmazonS3::getCommandLine($this->config())
        );
    }

    public function testUnlock(): void
    {
        $this::assertSame([self::BINARY, 'unlock'], Unlock::getCommandLine($this->config()));
    }

    public function testUnlockAmazonS3(): void
    {
        $this::assertSame(
            [self::BINARY, 'unlock', '--repo='.self::ENDPOINT],
            UnlockAmazonS3::getCommandLine($this->config())
        );
    }

    public function testListObjects(): void
    {
        $this::assertSame(
            [self::BINARY, '--repo='.self::REPO, 'list'],
            ListObjects::getCommandLine($this->config())
        );
    }

    /**
     * Regression for F-01: shell metacharacters stay inside one argv element
     * and are never interpreted.
     */
    public function testShellMetacharactersStayInOneArgument(): void
    {
        $configuration = $this->config();
        $configuration->setBackupPath('/srv/data; touch /tmp/pwned');

        $arguments = Backup::getCommandLine($configuration);

        $this::assertSame('/srv/data; touch /tmp/pwned', $arguments[array_key_last($arguments)]);
        $this::assertContains('--', $arguments);
        foreach ($arguments as $argument) {
            $this::assertIsString($argument);
        }
    }
}
```

- [ ] **Step 2: Test laufen lassen und Fehlschlag bestätigen**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml tests/Library/CommandLine/CommandBuilderTest.php`

Expected: FAIL. Alle 22 Tests scheitern mit „Failed asserting that two variables are identical" — die Klassen liefern heute Strings.

- [ ] **Step 3: CommandLineInterface umstellen**

Modify `src/Library/CommandLine/CommandLineInterface.php`:

```php
interface CommandLineInterface
{
    /**
     * @param Configuration $configuration
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array;

    /**
     * @param Configuration $configuration
     * @return array<string,string|null>
     */
    public static function getEnvParameters(Configuration $configuration): array;
}
```

- [ ] **Step 4: CommandLineFactory umstellen**

Modify `src/Library/CommandLine/CommandLineFactory.php` — `createCommandLine()` ersetzen. `createEnvParameters()` bleibt unverändert:

```php
    /**
     * @return list<string>
     * @throws InvalidConfigurationException
     */
    public function createCommandLine(Configuration $configuration, Commands $command): array
    {
        $callable = ['MuckiRestic\Library\CommandLine\Commands\\'.$command->value, 'getCommandLine'];
        if (!is_callable($callable)) {
            throw new InvalidConfigurationException('Invalid command class or method.');
        }

        $arguments = call_user_func($callable, $configuration);
        if (!is_array($arguments)) {
            throw new InvalidConfigurationException(
                'Command '.$command->value.' did not return an argument list.'
            );
        }

        $commandLine = [];
        foreach ($arguments as $argument) {
            if (!is_string($argument)) {
                throw new InvalidConfigurationException(
                    'Command '.$command->value.' returned a non-string argument.'
                );
            }
            $commandLine[] = $argument;
        }

        return $commandLine;
    }
```

Die Schleife ist nicht nur für PHPStan da: sie verhindert, dass eine fremde Kommandoklasse verschachtelte Arrays oder Objekte in die Prozessargumente einschleust.

- [ ] **Step 5: Client umstellen**

Modify `src/Client.php`. `getProcess()` und `requestVersion()` ersetzen und die beiden Aufrufer in `getResticVersion()` anpassen:

```php
    /**
     * @param list<string> $command
     * @param array<string,string|null> $envParameters
     * @return Process
     */
    public function getProcess(array $command, array $envParameters = []): Process
    {
        return new Process($command, null, $envParameters, null, 1000);
    }

    /**
     * @param list<string> $arguments
     * @return Process
     */
    public function requestVersion(array $arguments): Process
    {
        $process = $this->getProcess(array_merge([$this->resticBinaryPath], $arguments));
        $process->run();

        return $process;
    }
```

In `getResticVersion()` die beiden Aufrufe ändern:

```php
        $process = $this->requestVersion(['version']);
```

und

```php
            $versionRawResult = OutputParser::fixJsonOutput(
                $this->requestVersion(['version', '--json'])->getOutput()
            );
```

Der Timeout bleibt bei `1000` — Befund B-1 ist nicht Teil dieses Releases.

- [ ] **Step 6: Configuration umstellen**

Modify `src/Library/Configuration.php` — `getCommandStringByCommand()` umbenennen und `createProcess()` anpassen:

```php
    /**
     * @return list<string>
     * @throws InvalidConfigurationException
     */
    public function getCommandArgumentsByCommand(Commands $command): array
    {
        $commandLineFactory = new CommandLineFactory();
        return $commandLineFactory->createCommandLine($this, $command);
    }
```

```php
    /**
     * @throws InvalidConfigurationException
     */
    public function createProcess(Commands $commands): Process
    {
        return $this->getProcess(
            $this->getCommandArgumentsByCommand($commands),
            $this->getEnvParametersByCommand($commands)
        );
    }
```

Run danach: `ddev exec grep -rn "getCommandStringByCommand" src/ tests/`
Expected: nur noch Treffer in `tests/Library/CommandLine/Commands/ForgetTest.php` — der wird in Step 12 angepasst.

- [ ] **Step 7: Init, InitAmazonS3, ListObjects umstellen**

Jeweils den Rumpf von `getCommandLine()` ersetzen, Rückgabetyp auf `array`, PHPDoc `@return list<string>` ergänzen.

`src/Library/CommandLine/Commands/Init.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            'init',
            '--repo='.$configuration->getRepositoryPath(),
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }

        return $command;
    }
```

`src/Library/CommandLine/Commands/InitAmazonS3.php` — identisch, aber `'--repo='.$configuration->getAwsS3Endpoint()`.

`src/Library/CommandLine/Commands/ListObjects.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [
            $configuration->getBinaryPath(),
            '--repo='.$configuration->getRepositoryPath(),
            'list',
        ];
    }
```

- [ ] **Step 8: Backup und BackupAmazonS3 umstellen**

`src/Library/CommandLine/Commands/Backup.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            '--repo='.$configuration->getRepositoryPath(),
            'backup',
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }
        if ($configuration->isCompress()) {
            $command[] = '--compression=auto';
        }
        if ($configuration->getHostName()) {
            $command[] = '--host='.$configuration->getHostName();
        }
        foreach ($configuration->getTags() as $tag) {
            $command[] = '--tag='.$tag;
        }

        $command[] = '--';
        $command[] = (string) $configuration->getBackupPath();

        return $command;
    }
```

`src/Library/CommandLine/Commands/BackupAmazonS3.php` — identisch, aber `'--repo='.$configuration->getAwsS3Endpoint()`.

`getBackupPath()` liefert `?string`; der Cast hält PHPStan Level 8 zufrieden. Ein fehlender Backup-Pfad wird bereits von `checkInputParametersByCommand()` abgefangen, bevor die Kommandozeile gebaut wird.

- [ ] **Step 9: Check, Prune, Stats, Unlock und ihre AmazonS3-Varianten umstellen**

`Check.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [$configuration->getBinaryPath(), 'check', '--read-data'];
    }
```

`CheckAmazonS3.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [
            $configuration->getBinaryPath(),
            '--repo='.$configuration->getAwsS3Endpoint(),
            'check',
            '--read-data',
        ];
    }
```

`Prune.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [$configuration->getBinaryPath(), 'prune'];
    }
```

`PruneAmazonS3.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [
            $configuration->getBinaryPath(),
            'prune',
            '--repo='.$configuration->getAwsS3Endpoint(),
        ];
    }
```

`Stats.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [$configuration->getBinaryPath(), 'stats'];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }

        return $command;
    }
```

`StatsAmazonS3.php` — **hier zusätzlich Befund B-5 beheben**: bisher `getRepositoryPath()`, neu `getAwsS3Endpoint()`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            '--repo='.$configuration->getAwsS3Endpoint(),
            'stats',
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }

        return $command;
    }
```

`Unlock.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [$configuration->getBinaryPath(), 'unlock'];
    }
```

`UnlockAmazonS3.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [
            $configuration->getBinaryPath(),
            'unlock',
            '--repo='.$configuration->getAwsS3Endpoint(),
        ];
    }
```

`Check`, `Prune`, `Stats` und `Unlock` übergeben das Repository weiterhin über `RESTIC_REPOSITORY` aus `getEnvParameters()` — deshalb steht dort kein `--repo=`. Das ist bestehendes Verhalten und bleibt so.

- [ ] **Step 10: Snapshots und SnapshotsAmazonS3 umstellen**

`Snapshots.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            '--repo='.$configuration->getRepositoryPath(),
            'snapshots',
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }
        if ($configuration->getHostName()) {
            $command[] = '--host='.$configuration->getHostName();
        }

        return $command;
    }
```

`SnapshotsAmazonS3.php` — identisch, aber `'--repo='.$configuration->getAwsS3Endpoint()`.

- [ ] **Step 11: Forget, ForgetAmazonS3, SingleForget, SingleForgetAmazonS3 umstellen**

`Forget.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            'forget',
            '--repo='.$configuration->getRepositoryPath(),
            '--prune',
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }
        if ($configuration->getHostName()) {
            $command[] = '--host='.$configuration->getHostName();
        }
        if ($configuration->getKeepDaily() > 0) {
            $command[] = '--keep-daily='.$configuration->getKeepDaily();
        }
        if ($configuration->getKeepWeekly() > 0) {
            $command[] = '--keep-weekly='.$configuration->getKeepWeekly();
        }
        if ($configuration->getKeepMonthly() > 0) {
            $command[] = '--keep-monthly='.$configuration->getKeepMonthly();
        }
        if ($configuration->getKeepYearly() > 0) {
            $command[] = '--keep-yearly='.$configuration->getKeepYearly();
        }
        if ($configuration->getKeepLast() > 0) {
            $command[] = '--keep-last='.$configuration->getKeepLast();
        }
        if ($configuration->getGroupBy()) {
            $command[] = '--group-by='.$configuration->getGroupBy();
        }
        foreach ($configuration->getTags() as $tag) {
            $command[] = '--tag='.$tag;
        }

        if ($configuration->getSnapshotIds()) {
            $command[] = '--';
            foreach ($configuration->getSnapshotIds() as $snapshotId) {
                $command[] = $snapshotId;
            }
        }

        return $command;
    }
```

Die Snapshot-IDs standen bisher direkt hinter `--prune`, jetzt hinter `--` am Ende. Cobra wertet beides identisch aus. Die doppelte Prüfung `getKeepDaily() && getKeepDaily() > 0` aus dem Altcode entfällt — `> 0` genügt.

`ForgetAmazonS3.php` — identisch, aber `'--repo='.$configuration->getAwsS3Endpoint()`.

`SingleForget.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        return [
            $configuration->getBinaryPath(),
            'forget',
            '--repo='.$configuration->getRepositoryPath(),
            '--',
            (string) $configuration->getSnapshotId(),
        ];
    }
```

`SingleForgetAmazonS3.php` — identisch, aber `'--repo='.$configuration->getAwsS3Endpoint()`.

Der auskommentierte `--json`-Block bleibt als Kommentar erhalten; er dokumentiert, warum `SingleForget` kein JSON liefert.

- [ ] **Step 12: Restore, RestoreAmazonS3 und ForgetTest umstellen**

`Restore.php`:

```php
    /**
     * @return list<string>
     */
    public static function getCommandLine(Configuration $configuration): array
    {
        $command = [
            $configuration->getBinaryPath(),
            'restore',
            '--repo='.$configuration->getRepositoryPath(),
            '--target='.$configuration->getRestoreTarget(),
        ];

        if ($configuration->isJsonOutput()) {
            $command[] = '--json';
        }

        $command[] = '--';
        $command[] = $configuration->getRestoreItem();

        return $command;
    }
```

`RestoreAmazonS3.php` — identisch, aber `'--repo='.$configuration->getAwsS3Endpoint()`.

Modify `tests/Library/CommandLine/Commands/ForgetTest.php` — die Assertion ersetzen:

```php
        $forgetCommand = $manage->getCommandArgumentsByCommand(Commands::FORGET);

        $this::assertSame([
            TestData::RESTIC_TEST_PATH_0_18,
            'forget',
            '--repo=./var/testRep',
            '--prune',
            '--json',
        ], $forgetCommand);
```

Der bisherige Test setzt weder Host, Keep-Werte, Tags noch Snapshot-IDs, deshalb bleiben nur die fünf Elemente übrig.

- [ ] **Step 13: Unit-Suite laufen lassen**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml`

Expected: PASS. `CommandBuilderTest` mit 22 Tests grün, `ForgetTest` grün, keine Regression in `HelperTest` und `ConfigurationValidationTest`.

- [ ] **Step 14: Integrationstests laufen lassen**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit.xml`

Expected: 4 Fehler, ausschließlich `IntegrationAwsS3Test::testIntegration015/016/017/018` mit `Missing required parameter awsS3Endpoint` (leere `.env.test`). Alle vier `IntegrationTest`-Fälle müssen **grün** sein.

Dieser Schritt ist der eigentliche Beweis, dass `new Process(['./bin/restic_0.15.2_linux_386', …])` über `execvp()` mit relativen Binärpfaden funktioniert. Schlägt `IntegrationTest` mit „No such file or directory" fehl, ist der relative Pfad das Problem — dann in `Client::getProcess()` das Arbeitsverzeichnis explizit auf `getcwd()` setzen (zweiter Konstruktorparameter) und erneut prüfen.

- [ ] **Step 15: Suche nach verbliebenen Shell-Aufrufen**

Run: `ddev exec grep -rn "fromShellCommandline" src/ tests/`
Expected: keine Treffer.

Run: `ddev exec grep -rn "sprintf" src/Library/CommandLine/Commands/`
Expected: keine Treffer.

---

### Task 4: PHPStan-Fehler in Backup/AmazonS3 beheben

**Files:**
- Modify: `src/Library/Backup/AmazonS3.php:167-190` (`removeOldRepository()`)

**Interfaces:**
- Consumes: nichts
- Produces: keine neue öffentliche API. `removeOldRepository()` wirft künftig `InvalidConfigurationException`, wenn kein Bucket-Name gesetzt ist.

- [ ] **Step 1: Fehler reproduzieren**

Run: `ddev exec ./vendor/bin/phpstan analyse -n --no-progress`

Expected: FAIL mit „Found 2 errors" — `argument.type` in Zeile 172 und 183, jeweils „Offset 'Bucket' (string) does not accept type string|null".

- [ ] **Step 2: Null-Guard einziehen**

Modify `src/Library/Backup/AmazonS3.php` — am Anfang von `removeOldRepository()` einfügen, vor `$s3client = $this->getS3Client();`:

```php
        $bucketName = $this->getAwsS3BucketName();
        if ($bucketName === null) {
            throw new InvalidConfigurationException('Missing required parameter awsS3BucketName');
        }
```

Danach beide Aufrufe auf die lokale Variable umstellen:

```php
        $bucketObjects = $s3client->listObjectsV2([
            'Bucket' => $bucketName,
        ]);
```

und im `deleteObjects()`-Aufruf ebenfalls `'Bucket' => $bucketName`.

`InvalidConfigurationException` ist in der Datei bereits importiert.

- [ ] **Step 3: PHPStan erneut laufen lassen**

Run: `ddev exec ./vendor/bin/phpstan analyse -n --no-progress`

Expected: PASS, „No errors".

- [ ] **Step 4: Keine Regression**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml`

Expected: PASS.

---

### Task 5: Version, CHANGELOG und README

**Files:**
- Modify: `composer.json:4`
- Create: `CHANGELOG.md`
- Modify: `README.md` (Abschnitt „Usage", Migrationshinweis)

**Interfaces:**
- Consumes: die fertigen Änderungen aus Task 1–4
- Produces: keine Code-API

- [ ] **Step 1: Version anheben**

Modify `composer.json` — `"version": "v1.4.1"` wird zu `"version": "v1.5.0"`.

- [ ] **Step 2: CHANGELOG anlegen**

Create `CHANGELOG.md`:

```markdown
# Changelog

All notable changes to this project are documented in this file.

## [1.5.0] - 2026-09-17

### Security

- **Command injection in every restic call (critical).** Command lines were assembled
  as strings and passed to `Process::fromShellCommandline()`, which hands them to
  `/bin/sh -c` without escaping. Every value reaching a command — `repositoryPath`,
  `backupPath`, `restoreTarget`, `restoreItem`, `snapshotId`, `hostName`, `groupBy`,
  `tags` and `awsS3Endpoint` — could execute arbitrary shell commands. Commands are now
  built as argument lists and executed with `new Process()`, which calls `execve()`
  directly. No shell is involved.
- **Argument injection.** Option values are emitted as single `--flag=value` tokens and
  positional arguments are preceded by `--`. Configuration setters reject values
  starting with a dash.
- **Symlink traversal in `Helper::deleteDirectory()` (high).** The method resolved
  symlinks via `getRealPath()` and deleted their targets, so a symlink inside a
  repository directory could delete data outside it. Symlinks are now removed as links;
  their targets are never touched. A `realpath()` check refuses any path outside the
  base directory.

### Changed

- **BREAKING** `CommandLineInterface::getCommandLine()` returns `list<string>` instead
  of `string`.
- **BREAKING** `Client::getProcess()` takes `list<string>` instead of `string`.
- **BREAKING** `Client::requestVersion()` takes `list<string>` instead of `string`.
- **BREAKING** `Configuration::getCommandStringByCommand()` is renamed to
  `getCommandArgumentsByCommand()` and returns `list<string>`.
- `StatsAmazonS3` now uses `getAwsS3Endpoint()` instead of `getRepositoryPath()`,
  consistent with every other AmazonS3 command.
- `Helper::deleteDirectory()` returns `false` when the given path is itself a symlink,
  instead of deleting the target. A tree containing symlinks can now be deleted
  completely; previously the final `rmdir()` failed.

### Fixed

- Two PHPStan level 8 errors in `Backup\AmazonS3::removeOldRepository()`; the bucket
  name is now checked before it reaches the AWS SDK.

### Migration

The public facades `Backup`, `Manage` and `Restore` are unchanged — code that only uses
them needs no changes.

Two things to check:

1. Custom classes implementing `CommandLineInterface` must return an array. Build
   option values as `'--flag='.$value` (one element) and place `'--'` before positional
   arguments.
2. `InvalidConfigurationException` can now be thrown from configuration setters, not
   only during execution. Widen `try` blocks to cover the configuration phase.
```

- [ ] **Step 3: README anpassen**

Modify `README.md`. Nach dem Abschnitt „Installation" einen Migrationshinweis einfügen:

```markdown
## Upgrading to 1.5.0

Version 1.5.0 fixes a critical command injection. Commands are no longer assembled as
shell strings but as argument lists executed without a shell.

If you only use `Backup`, `Manage` and `Restore`, nothing changes. If you implement
`CommandLineInterface` yourself, `getCommandLine()` now returns `array`:

```php
public static function getCommandLine(Configuration $configuration): array
{
    $command = [
        $configuration->getBinaryPath(),
        '--repo='.$configuration->getRepositoryPath(),
        'backup',
    ];

    if ($configuration->isJsonOutput()) {
        $command[] = '--json';
    }

    $command[] = '--';
    $command[] = (string) $configuration->getBackupPath();

    return $command;
}
```

Option values must be a single element (`'--host='.$host`, not `'--host', $host`), and
positional arguments belong after `'--'`. Configuration setters reject values starting
with a dash and throw `InvalidConfigurationException`.
```

- [ ] **Step 4: Abschlussprüfung**

Run: `ddev exec ./vendor/bin/phpunit --configuration=phpunit.xml`
Expected: 4 Fehler, ausschließlich `IntegrationAwsS3Test` wegen leerer `.env.test`.

Run: `ddev exec ./vendor/bin/phpstan analyse -n --no-progress`
Expected: „No errors".

Run: `git status --short`
Expected: geänderte und neue Dateien, nichts committet. Der Maintainer übernimmt das Committen.

---

## Self-Review

**Spec-Abdeckung:**

| Spec-Abschnitt | Task |
|---|---|
| Geänderte Signaturen (Interface, Factory, Client, Configuration) | 3, Steps 3–6 |
| Argument Injection: `--flag=wert` | 3, Steps 7–12 |
| Argument Injection: `--` vor Positionsargumenten | 3, Steps 8, 11, 12 |
| Argument Injection: Dash-Abweisung in 13 Settern | 2 |
| `InvalidConfigurationException::optionLikeValue()` | 2, Step 3 |
| Kommando-Definitionen aller 21 Klassen | 3, Steps 7–12 |
| `Helper::deleteDirectory()` inklusive `assertWithin()` | 1 |
| Tests: `HelperTest` (6 Fälle) | 1, Step 1 |
| Tests: `CommandBuilderTest` (21 Klassen) | 3, Step 1 |
| Tests: `ConfigurationValidationTest` (13 Setter) | 2, Step 1 |
| Tests: Injection-Regression | 3, Step 1 (`testShellMetacharactersStayInOneArgument`) |
| Tests: `ForgetTest` angepasst | 3, Step 12 |
| Risiko `execvp` mit relativen Pfaden | 3, Step 14 inklusive Rückfallplan |
| B-5 `StatsAmazonS3` | 3, Step 9 |
| PHPStan-Null-Guard | 4 |
| `composer.json` 1.5.0 | 5, Step 1 |
| `CHANGELOG.md` mit Migrationshinweis | 5, Step 2 |
| README Array-Form | 5, Step 3 |
| Nichts committen | Global Constraints, 5 Step 4 |

Keine Lücke.

**Typkonsistenz:** `getCommandLine()` heißt in Interface, allen 21 Klassen und im Test gleich. `getCommandArgumentsByCommand()` wird in Task 3 Step 6 eingeführt und in Step 12 (`ForgetTest`) verwendet — kein abweichender Name. `assertNotOptionLike()` wird in Task 2 definiert und nur dort verwendet. `assertWithin()` wird in Task 1 definiert und nur dort verwendet. `optionLikeValue()` wird in Task 2 Step 3 definiert und in Step 4 verwendet.

**Testzahlen:** Nach Task 1 sind es 29, nach Task 2 57, nach Task 3 unverändert 57 plus die 22 aus `CommandBuilderTest` — also 79 in der Unit-Suite. Die Zahlen in den Erwartungen der Steps sind entsprechend gesetzt; weicht die tatsächliche Zahl ab, ist das zu klären und nicht stillschweigend anzupassen.
