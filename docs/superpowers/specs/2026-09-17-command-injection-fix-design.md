# Command Injection beseitigen (F-01) und Symlink-Traversal in deleteDirectory (S-2)

**Datum:** 2026-09-17
**Zielversion:** muckiware/restic 1.5.0
**Status:** Entwurf zur Abnahme

## Ausgangslage

Eine Sicherheits- und Betriebsanalyse des Shopware-Plugins `muckiware/facility-plugin`
hat als schwersten Befund (F-01, kritisch) eine Command Injection in dieser Library
ermittelt. Zwei Befunde werden hier behoben:

**F-01 — Command Injection (kritisch).** Alle 21 Kommandoklassen unter
`src/Library/CommandLine/Commands/` bauen die restic-Kommandozeile als String per
`sprintf()` und `.=` zusammen. `Client::getProcess()` übergibt diesen String an
`Process::fromShellCommandline()`, das ihn unverändert an `/bin/sh -c` weiterreicht.
Es findet an keiner Stelle ein Escaping statt.

Ungefiltert in die Shell gelangen: `binaryPath`, `repositoryPath`, `backupPath`,
`restoreTarget`, `restoreItem`, `snapshotId`, `snapshotIds[]`, `hostName`, `groupBy`,
`tags[]` und `awsS3Endpoint`. Im Shopware-Plugin stammen diese Werte aus der Tabelle
`muwa_backup_repository` und teilweise direkt aus dem Request-Body
(`POST /api/_action/muwa/backup/process`). Ein Pfad der Form `/tmp; <befehl>` führt zu
Remote Code Execution mit den Rechten des PHP-Prozesses.

**S-2 — Symlink-Traversal in `Helper::deleteDirectory()` (hoch).** Die Methode iteriert
mit `RecursiveIteratorIterator` und löscht über `$item->getRealPath()`. Für einen
Symlink liefert `getRealPath()` das aufgelöste Ziel, sodass `unlink()` bzw. `rmdir()`
Daten **außerhalb** des Zielverzeichnisses treffen. Der Symlink selbst bleibt liegen,
weshalb der abschließende `rmdir()` mit „Directory not empty" fehlschlägt und eine
`ActionException` wirft — nachdem bereits fremde Daten gelöscht wurden.

Verifiziert mit folgendem Aufbau: ein Verzeichnis `repo/` mit `link_auf_opfer ->
../opfer` und `link_auf_datei -> ../opfer/WICHTIG.txt`. Nach
`Helper::deleteDirectory('.../repo')` waren `opfer/` und `opfer/WICHTIG.txt` gelöscht,
und die Methode warf zusätzlich eine `ActionException`.

Aufgerufen wird die Methode aus `Backup\Local::createRepository($overwrite = true)` mit
dem `repositoryPath` — also demselben nicht vertrauenswürdigen Wert wie bei F-01.

## Getroffene Entscheidungen

| Frage | Entscheidung |
|---|---|
| API-Kompatibilität | Harter Schnitt auf `array<string>`, kein BC-Layer. Minor-Bump auf 1.5.0. |
| Umfang | F-01 und S-2. Andere Befunde der Analyse bleiben in diesem Release offen. |
| Argument Injection | `--flag=wert` als Einzeltoken plus `--` vor Positionsargumenten plus Abweisung von Werten mit führendem `-`. |
| Symlink-Verhalten | Den Link selbst entfernen, das Ziel nie anfassen. Zusätzlich eine `realpath()`-Schranke als zweite Verteidigungslinie. |

Der harte Schnitt trifft nur, wer eigene Kommandoklassen gegen
`CommandLineInterface` implementiert oder `Client::getProcess()` direkt aufruft. Das
Shopware-Plugin nutzt ausschließlich die Facades `Backup`, `Manage` und `Restore`,
deren Signaturen unverändert bleiben.

## Architektur

Die Ausführungskette bleibt unverändert; nur der Datentyp zwischen Kommandoklasse und
Prozess wechselt von `string` auf `list<string>`:

```
Backup|Manage|Restore
  └─ *Factory
      └─ Backup\Local | Backup\AmazonS3 | …
          └─ Configuration::createProcess(Commands::X)
              └─ CommandLineFactory
                  └─ CommandLine\Commands\X::getCommandLine()   string  →  list<string>
              └─ Client::getProcess()                            string  →  list<string>
                  └─ new Process(...)  statt  Process::fromShellCommandline(...)
```

`new Process(array $command)` ruft über `proc_open()` direkt `execvp()` auf. Es gibt
keine Shell im Spiel, damit ist Shell-Injection strukturell ausgeschlossen und nicht
nur weggefiltert.

### Geänderte Signaturen

```php
// src/Library/CommandLine/CommandLineInterface.php
/** @return list<string> */
public static function getCommandLine(Configuration $configuration): array;

// src/Library/CommandLine/CommandLineFactory.php
/** @return list<string> */
public function createCommandLine(Configuration $c, Commands $command): array;

// src/Client.php
/**
 * @param list<string> $command
 * @param array<string,string|null> $envParameters
 */
public function getProcess(array $command, array $envParameters = []): Process;

/** @param list<string> $arguments */
public function requestVersion(array $arguments): Process;

// src/Library/Configuration.php
/** @return list<string> */
public function getCommandArgumentsByCommand(Commands $command): array;
```

`getCommandStringByCommand()` wird zu `getCommandArgumentsByCommand()` umbenannt — der
alte Name wäre bei einem Array irreführend.

`requestVersion()` wechselt von `string` auf `list<string>`, weil heute
`requestVersion('version --json')` zwei Argumente in einem String übergibt. Neu:
`requestVersion(['version'])` und `requestVersion(['version', '--json'])`.

`Client::getProcess()` bleibt `public`. Die Einschränkung der Sichtbarkeit (Befund S-4)
ist ein eigener Bruch und gehört nicht in dieses Release.

### Unverändert

`getEnvParameters()` liefert bereits ein Array und bleibt wie es ist. Secrets
(`RESTIC_PASSWORD`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`)
werden weiterhin über die Prozessumgebung übergeben und tauchen nicht in argv auf.

`ResultEntity::getCommandLine()` bleibt `?string`. Symfony liefert auch bei
Array-Prozessen über `Process::getCommandLine()` einen String, sodass die 24
`assertIsString()`-Zusicherungen in den Integrationstests gültig bleiben.

Der Prozess-Timeout bleibt bei 1000 Sekunden. Ihn konfigurierbar zu machen ist Befund
B-1 (Plugin-Analyse F-13) und nicht Teil dieses Releases.

## Argument Injection

Der Wechsel auf `execve()` beseitigt Shell-Injection, nicht aber Argument Injection:
ein `backupPath` mit dem Wert `--exclude=/etc` wird von restic als Option gelesen, nicht
als Pfad. Drei Mechanismen dagegen.

**1. Optionswerte als Einzeltoken.** Statt `['-r', $repo]` wird `['--repo=' . $repo]`
erzeugt. Ein separates Werttoken kann von Cobra als Flag fehlinterpretiert werden,
`--flag=wert` nie. Betrifft `--repo`, `--host`, `--tag`, `--group-by`, `--target` und
`--compression`.

**2. `--` vor Positionsargumenten.** Bei `backup <pfad>`, `restore <snapshot>` und
`forget <ids...>` steht `--` vor dem ersten Positionsargument. Alles danach wird von
Cobra als Wert behandelt, unabhängig von führenden Bindestrichen.

**3. Abweisung von Werten mit führendem `-`.** Die folgenden Setter werfen eine
`InvalidConfigurationException`, wenn der Wert mit `-` beginnt:

`setBinaryPath`, `setRepositoryPath`, `setBackupPath`, `setRestoreTarget`,
`setRestoreItem`, `setSnapshotId`, `addSnapshotId`, `setSnapshotIds`, `setHostName`,
`setGroupBy`, `setTag`, `setTags`, `setAwsS3Endpoint`.

Nicht geprüft werden Werte, die nie in argv landen: `repositoryPassword`,
`awsAccessKeyId`, `awsSecretAccessKey`, `awsS3Region` (Prozessumgebung),
`awsS3BucketName` (AWS SDK) sowie `sftpUsername` und `sftpPassword` (derzeit ungenutzt).

Die Prüfung sitzt im Setter statt in `checkInputParametersByCommand()`, damit der
Fehler dort auftritt, wo der Wert hereinkommt, und nicht erst kurz vor der Ausführung.

`InvalidConfigurationException` erhält dafür einen benannten Konstruktor:

```php
public static function optionLikeValue(string $parameter, string $value): self
{
    return new self(sprintf(
        'Parameter %s must not start with a dash, got "%s"', $parameter, $value
    ));
}
```

## Kommando-Definitionen

Die Zielform aller 21 Klassen. `$b` = `getBinaryPath()`, `$repo` = `getRepositoryPath()`
bzw. `getAwsS3Endpoint()` bei den AmazonS3-Varianten.

| Klasse | Array |
|---|---|
| `Init` / `InitAmazonS3` | `[$b, 'init', '--repo='.$repo]` + `['--json']` |
| `Backup` / `BackupAmazonS3` | `[$b, '--repo='.$repo, 'backup']` + `['--json']` + `['--compression=auto']` + `['--host='.$h]` + `['--tag='.$t]`* + `['--', $backupPath]` |
| `Check` | `[$b, 'check', '--read-data']` |
| `CheckAmazonS3` | `[$b, '--repo='.$repo, 'check', '--read-data']` |
| `Snapshots` / `SnapshotsAmazonS3` | `[$b, '--repo='.$repo, 'snapshots']` + `['--json']` + `['--host='.$h]` |
| `Forget` / `ForgetAmazonS3` | `[$b, 'forget', '--repo='.$repo, '--prune']` + `['--json']` + `['--host='.$h]` + `['--keep-daily='.$n]` … + `['--group-by='.$g]` + `['--tag='.$t]`* + `['--', ...$snapshotIds]` |
| `SingleForget` / `SingleForgetAmazonS3` | `[$b, 'forget', '--repo='.$repo, '--', $snapshotId]` |
| `Restore` / `RestoreAmazonS3` | `[$b, 'restore', '--repo='.$repo, '--target='.$target]` + `['--json']` + `['--', $restoreItem]` |
| `Prune` | `[$b, 'prune']` |
| `PruneAmazonS3` | `[$b, 'prune', '--repo='.$repo]` |
| `Stats` | `[$b, 'stats']` + `['--json']` |
| `StatsAmazonS3` | `[$b, '--repo='.$repo, 'stats']` + `['--json']` — Korrektur, siehe unten |
| `Unlock` | `[$b, 'unlock']` |
| `UnlockAmazonS3` | `[$b, 'unlock', '--repo='.$repo]` |

Zwei Abweichungen vom bisherigen Verhalten sind beabsichtigt und funktional gleichwertig:

- Bei `Forget` standen die Snapshot-IDs bisher direkt hinter `--prune`, künftig stehen
  sie nach `--` am Ende. Cobra wertet beides identisch aus.
- `--compression auto` wird zu `--compression=auto`.

`StatsAmazonS3` verwendet heute als einzige AmazonS3-Klasse `getRepositoryPath()` statt
`getAwsS3Endpoint()`. Das ist Befund B-5 und wird im Zuge des Umbaus korrigiert; in der
Tabelle steht `$repo` deshalb wie bei allen anderen AmazonS3-Klassen für
`getAwsS3Endpoint()`.

`Check`, `Prune`, `Stats` und `Unlock` (jeweils die lokalen Varianten) übergeben das
Repository weiterhin nicht auf der Kommandozeile, sondern über `RESTIC_REPOSITORY` aus
`getEnvParameters()`. Das bleibt unverändert. Die AmazonS3-Varianten setzen
`RESTIC_REPOSITORY` nicht und übergeben das Repository ausschließlich über `--repo=`.

`ListObjects` erzeugt `[$b, '--repo='.$repo, 'list']`. Dass `restic list` ein
Pflichtargument erwartet und das Kommando damit unvollständig ist, bleibt als
bestehender Mangel erhalten — `Commands::LIST` wird von keiner Implementierungsklasse
aufgerufen.

## Helper::deleteDirectory()

```php
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
```

Drei Änderungen gegenüber heute:

1. `getPathname()` statt `getRealPath()`. Damit trifft `unlink()` den Symlink und nicht
   sein Ziel.
2. `isLink()` wird **vor** `isDir()` geprüft. Ein Symlink auf ein Verzeichnis meldet
   `isDir() === true`; ohne die Reihenfolge würde `rmdir()` das Ziel treffen.
3. `assertWithin()` prüft vor jedem Löschen, dass das enthaltende Verzeichnis unterhalb
   des per `realpath()` aufgelösten Basisverzeichnisses liegt, und wirft sonst eine
   `ActionException`.

```php
private static function assertWithin(string $base, string $path): void
{
    $parent = realpath(dirname($path));
    if ($parent === false
        || ($parent !== $base && !str_starts_with($parent, $base . DIRECTORY_SEPARATOR))
    ) {
        throw new ActionException(sprintf(
            'Refusing to delete path outside of %s: %s', $base, $path
        ));
    }
}
```

`RecursiveDirectoryIterator` steigt in symbolisch verlinkte Verzeichnisse
standardmäßig nicht ab (`hasChildren($allowLinks = false)`), sodass der Iterator selbst
den Zielbaum nicht betritt.

Zwei Verhaltensänderungen, die dokumentiert gehören:

- Ein Symlink, der als `$dir` übergeben wird, führt zu `return false` statt zum Löschen
  des Ziels.
- Ein Zielbaum mit Symlinks lässt sich künftig vollständig löschen; bisher scheiterte
  der abschließende `rmdir()`.

## Fehlerbehandlung

Unverändert: fehlgeschlagene Prozesse werfen weiterhin `ProcessFailedException`,
Konfigurationsfehler `InvalidConfigurationException`, Dateisystemfehler
`ActionException`. Neu ist ausschließlich, dass `InvalidConfigurationException` nun auch
aus den Settern kommen kann, nicht nur aus `checkInputParametersByCommand()`.

Aufrufer, die heute `InvalidConfigurationException` um den Ausführungsblock fangen,
müssen den Block gegebenenfalls auf die Konfigurationsphase ausweiten. Das gehört in den
Migrationsabschnitt der README.

## Tests

Testgetrieben: jeder Test wird vor der zugehörigen Änderung geschrieben und muss gegen
den heutigen Stand fehlschlagen.

**Neu — `tests/Service/HelperTest.php`**
- Symlink auf ein Verzeichnis außerhalb des Zielbaums: Ziel überlebt, Link ist weg.
- Symlink auf eine Datei außerhalb des Zielbaums: Ziel überlebt, Link ist weg.
- Verschachtelter Symlink in einem Unterverzeichnis.
- Zielbaum ohne Symlinks wird vollständig gelöscht, Rückgabe `true`.
- `$dir` ist selbst ein Symlink: Rückgabe `false`, Ziel unangetastet.
- Nicht existierendes Verzeichnis: Rückgabe `false`.

Die ersten beiden Fälle sind gegen den heutigen Code bereits als fehlschlagend
nachgewiesen.

**Neu — `tests/Library/CommandLine/CommandBuilderTest.php`**
Ein Datenprovider über alle 21 Kommandoklassen mit vollständig konfigurierter
`Configuration` und dem erwarteten Array, inklusive `--repo=` und `--`.

**Neu — `tests/Library/ConfigurationValidationTest.php`**
Ein Datenprovider über alle 13 gehärteten Setter: Wert mit führendem `-` führt zu
`InvalidConfigurationException`; ein gültiger Wert wird akzeptiert.

**Neu — Injection-Regressionstest** in `CommandBuilderTest`
`setBackupPath('/tmp/x; touch /tmp/pwned')` erzeugt genau ein argv-Element mit diesem
Inhalt; die Marker-Datei entsteht bei Ausführung nicht.

**Angepasst — `tests/Library/CommandLine/Commands/ForgetTest.php`**
Prüft heute die exakte Kommandozeile als String und wird auf das Array umgestellt.

**Unverändert** — die Integrationstests. Sie prüfen Ergebnisse, nicht Kommandozeilen,
und sind die eigentliche Absicherung, dass die Array-Form gegen echte restic-Binaries
funktioniert.

## Risiko

`new Process(['./bin/restic_0.15.2_linux_386', …])` geht über `execvp()` statt über die
Shell. Ein relativer Pfad mit Slash wird dabei gegen das Arbeitsverzeichnis aufgelöst,
nicht über `PATH` — das entspricht dem bisherigen Verhalten. Verifiziert wird das durch
die Integrationstests, die genau diese relativen Binärpfade verwenden und lokal grün
laufen.

## Nicht in diesem Release

Aus der Analyse bewusst zurückgestellt: S-3 (114 MB Binaries im Composer-Paket, fehlende
`.gitattributes`), S-4 (`getProcess()` public), S-5 (keine Validierung des Binärpfads
über die Dash-Prüfung hinaus), S-6 (`RESTIC_PASSWORD_FILE`), S-7
(`removeOldRepository()` leert den gesamten Bucket), B-1 (Timeout), B-2
(Output-Pufferung), B-3 (`prune` vor jedem Backup), B-4 (`empty()`-Prüfung), B-6
(`RepositoryLocationTypes` ohne Implementierung), B-7 (`strict_types`).

## Zusätzlich aufgenommen

Zwei Punkte wurden nach der ersten Fassung bewusst in den Umfang gezogen:

**B-5 — `StatsAmazonS3`.** Die Klasse wird auf `getAwsS3Endpoint()` korrigiert, passend
zu allen anderen AmazonS3-Klassen. Das ist eine Verhaltensänderung und gehört in den
CHANGELOG-Eintrag zu 1.5.0. Ein Testfall in `CommandBuilderTest` sichert die Korrektur
ab.

**PHPStan-Fehler in `Backup/AmazonS3.php`.** `getAwsS3BucketName()` liefert
`string|null`, `S3Client::listObjectsV2()` und `deleteObjects()` erwarten `string`. Vor
beiden Aufrufen wird der Bucket-Name geprüft und bei `null` eine
`InvalidConfigurationException` geworfen. Damit läuft `composer run-script phpstan`
wieder fehlerfrei und 1.5.0 kann mit grüner CI herausgehen.

## Git

Es wird nichts committet. Alle Änderungen bleiben als uncommittete Änderungen im
Working Tree; das Committen übernimmt der Maintainer.

## Lieferumfang

- 21 Kommandoklassen, `CommandLineInterface`, `CommandLineFactory`, `Client`,
  `Configuration`, `InvalidConfigurationException`, `Helper`
- `Backup/AmazonS3.php`: Null-Guard für den Bucket-Namen
- 3 neue Testdateien, 1 angepasste
- `composer.json` auf `1.5.0`
- `CHANGELOG.md` neu angelegt, mit Eintrag zu 1.5.0 inklusive Migrationshinweis und den
  beiden Verhaltensänderungen (`StatsAmazonS3`, `deleteDirectory`)
- README: Abschnitt zu eigenen Kommandoklassen auf die Array-Form umgestellt
