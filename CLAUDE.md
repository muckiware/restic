# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

`muckiware/restic` — a PHP 8.1+ library (Composer package, MIT) that wraps the [restic](https://github.com/restic/restic)
backup binary. It builds restic command lines, runs them through `symfony/process`, and maps the output into typed
result entities. It ships a small Symfony Console app on top as a demo/manual-test harness.

PSR-4: `MuckiRestic\` → `src/`, `MuckiRestic\Test\` → `tests/` (autoload-dev).

## Commands

Run everything **inside DDEV** (`.ddev/config.yaml`, generic type, PHP 8.4). The committed restic binaries are
`linux_386`, so the tests cannot execute on the macOS host; the installed `vendor/` also has a `>= 8.4.1` platform
check that the host PHP does not satisfy.

```bash
ddev start
ddev composer install

# Full test suite (includes integration tests that execute the restic binaries)
ddev exec ./vendor/bin/phpunit --configuration=phpunit.xml

# Without tests/Integration
ddev exec ./vendor/bin/phpunit --configuration=phpunit_without_integration.xml

# Single test class / single method
ddev exec ./vendor/bin/phpunit --configuration=phpunit.xml tests/Library/BackupTest.php
ddev exec ./vendor/bin/phpunit --configuration=phpunit.xml --filter testCheckCreateBackup

# Static analysis (PHPStan level 8, src/ only)
ddev composer run-script phpstan

# CLI app (requires dev autoload, see "Gotchas")
ddev exec bin/console muwa:restic:client --help
```

CI (`.github/workflows/main.yml`) runs the **full** phpunit config plus phpstan on PHP 8.1–8.4 and writes `.env.test`
from repository secrets.

### Test layout and prerequisites

`tests/Integration/` really executes the restic binaries committed under `bin/` (`restic_0.14.0` … `restic_0.18.0`).
`IntegrationAwsS3Test` additionally needs a `.env.test` with `AWS_REGION`, `AWS_ENDPOINT_URL`, `AWS_S3_BUCKET_NAME`,
`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`; the file is loaded by `tests/TestBootstrap.php` via `symfony/dotenv`
and is not committed. Tests write to the gitignored `var/testRep`, `var/testBackup`, `var/testRestore` and delete
those directories on setup.

`phpunit_without_integration.xml` is **not** a self-contained unit suite: `tests/Library/BackupTest::testCheckCreateBackup`
also shells out to restic and expects an existing repository in `var/testRep`, which only the integration suite
creates. Running the excluded config on a clean checkout therefore reports one error.

### macOS + DDEV requires mutagen — `O_NOATIME` breaks on the bind mount

**DDEV mutagen must stay enabled on macOS** (`performance_mode: mutagen`). Without it, every test that runs
`restic backup` against a source path inside the project fails with
`{"message_type":"error","error":{"Op":"read","Err":5}}` (EIO) and `Warning: at least one source file could not be read`
— all four `IntegrationTest` cases, all four `IntegrationAwsS3Test` cases and `BackupTest::testCheckCreateBackup`.

Root cause (traced with strace, reduced to a minimal repro): restic opens each source file
`O_RDONLY|O_NOFOLLOW|O_CLOEXEC` and then adds **`O_NOATIME`** via `fcntl(F_SETFL)`. Without mutagen DDEV mounts the
project as mount type `fakeowner` (Docker Desktop's macOS file sharing); there the `F_SETFL` succeeds but the
following `read()` returns `EIO`. Reading the same file without `O_NOATIME` works, and reading it with `O_NOATIME`
from the container's own filesystem works. It is not a code bug, not restic-version specific (0.15.2 and 0.18.0
behave identically) and not caused by the 32-bit binaries (a native arm64 restic 0.18.0 fails the same way).
`restic backup --with-atime` does not help — it only controls whether atime is stored in the snapshot.

If the EIO errors reappear, check `ddev mutagen status` first; "not enabled" is the cause. Re-enable with
`ddev config --performance-mode=mutagen && ddev restart`.

### Current expected results

With mutagen enabled: `phpunit_without_integration.xml` is green (23 tests), `phpunit.xml` reports 36 tests with
**4 errors** — the `IntegrationAwsS3Test` cases failing on `Missing required parameter awsS3Endpoint` because
`.env.test` is empty locally. Fill it with real S3 credentials to run those.

`composer run-script phpstan` currently reports **2 pre-existing errors** in `Backup/AmazonS3.php:172` and `:183`
(`string|null` passed to `S3Client::listObjectsV2()`/`deleteObjects()`, which want `string`). `composer.lock` is
gitignored, so CI resolves a fresh `aws/aws-sdk-php` and sees the same thing. Guard `getAwsS3BucketName()` before the
SDK calls if you touch that file.

## Architecture

The call chain for every operation is the same five hops:

```
Backup|Manage|Restore   (public facade, RepositoryLocationTypes argument)
  └─ *Factory           (resolves MuckiRestic\Library\<Domain>\<LocationType>, copies config via reflection)
      └─ Backup\Local | Backup\AmazonS3 | Manage\… | Restore\…   (validate → run Process → parse)
          └─ Configuration::createProcess(Commands::X)
              └─ CommandLineFactory  → CommandLine\Commands\<Commands::X->value>::getCommandLine()/::getEnvParameters()
                  └─ symfony/process → restic binary → ResultParser\* → Entity\Result\ResultEntity
```

### Layers

- **`src/Client.php`** — abstract base. Owns `getProcess()` and restic version detection (`getResticVersion()`).
  `create()` is a static late-static-bound constructor; the constructor is `final` and takes no arguments, so all
  state is set through setters.
- **`src/Library/Configuration.php`** — extends `Client`. Holds *every* configurable value as a `protected` property
  (repository path/password, backup path, keep-*, tags, host, snapshot ids, SFTP creds, AWS creds, flags).
  Also does `checkInputParametersByCommand()` validation and builds the `Process`.
- **`src/Library/Backup.php` / `Manage.php` / `Restore.php`** — thin public facades. Each method just delegates to its
  `*Factory` and passes a `RepositoryLocationTypes`.
- **`src/Library/<Domain>/<LocationType>.php`** — the actual implementations (`Backup\Local`, `Backup\AmazonS3`,
  `Manage\Local`, `Manage\AmazonS3`, `Restore\Local`, `Restore\AmazonS3`). They implement `BackupInterface`,
  `ManageInterface`, `RestoreInterface` and follow a fixed shape: validate config → `createProcess()` → `run()` →
  throw `ProcessFailedException` on failure → build a `ResultEntity`.
- **`src/Library/CommandLine/Commands/*.php`** — abstract classes with only static methods, one per entry in the
  `Commands` enum. `getCommandLine()` assembles the restic argument string; `getEnvParameters()` returns the env vars
  handed to the process. Secrets (`RESTIC_PASSWORD`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`,
  `AWS_DEFAULT_REGION`) travel via env, never on the command line — keep it that way when adding commands.
- **`src/ResultParser/*.php`** — extend `OutputParser`. Turn restic's stdout (JSON or text) into `ResultEntity`.
- **`src/Entity/`** — `Result\ResultEntity` (timings, command line, raw output, parsed `resticResponse`) plus
  `Result\ResticResponse\{Snapshot,Summary,Version}`, populated with `netresearch/jsonmapper`.
- **`src/Core/commandParameterConfiguration.json`** — declarative required-parameter list per command; the property
  names in it must match `Configuration`'s property names, since validation does `empty($this->{$parameter->getName()})`.

### Name-based dispatch (the main thing to know)

Two enums drive class resolution by string concatenation, so there is **no compile-time safety** anywhere in the chain:

- `Core\Commands` → `MuckiRestic\Library\CommandLine\Commands\{value}` (`CommandLineFactory`)
- `Core\RepositoryLocationTypes` → `MuckiRestic\Library\{Backup|Manage|Restore}\{value}` (`BackupFactory` etc.)

Amazon S3 variants are separate enum cases and separate classes (`BACKUP` vs `BACKUP_AMAZON_S3`), not a flag.
`RepositoryLocationTypes` also declares `SFTP`, `REST` and `S3` — those have **no implementation classes**, so
selecting them throws `InvalidRepLocationException` at runtime.

### Configuration propagation

`Backup`/`Manage`/`Restore` and their factories copy state to the next object with `ReflectionObject::getProperties()`
+ `setValue()`, skipping uninitialized and static properties. Consequence: any new configuration value **must** be a
non-static, non-readonly `protected` property on `Configuration` — a promoted constructor argument, a readonly
property, or state stored anywhere else will not survive the hop to the implementation class.

## Adding a new restic command

1. Add the case(s) to `Core\Commands` — normally a plain one and an `…_AMAZON_S3` one.
2. Add matching entries to `Core/commandParameterConfiguration.json` with the required `Configuration` property names.
3. Create `Library/CommandLine/Commands/<Value>.php` (+ `<Value>AmazonS3.php`) implementing `CommandLineInterface`.
4. Implement the operation in `Library/<Domain>/Local.php` and `AmazonS3.php`, add it to the domain interface.
5. Add the delegating method to the facade (`Backup`/`Manage`/`Restore`) and to the corresponding `*Factory`.
6. If the output needs structuring, add/extend a `ResultParser`.

## Version-dependent restic behaviour

restic changed its output across versions and the library branches on it:

- `Client::getResticVersion()` parses the text output first, then re-queries with `version --json` for **≥ 0.17.0**.
- `Restore\*` only parses JSON for **≥ 0.16.0** (`version_compare`).
- `Manage\*::resultsExecution()` uses `Service\Helper::checkBinaryVersion($v, $skipVersionCheck, '0.16.0')`.
  `$skipVersionCheck = true` bypasses that gate and is passed by `removeSnapshotById()`, `executePrune()` and
  `getRepositoryStats()` — the first two also force `$isJsonOutput = false`, `stats` keeps the configured flag.
  Don't "clean that up" into a uniform version check.
- restic's `--json` output is **newline-delimited JSON, not a JSON document**. `OutputParser::fixJsonOutput()`
  wraps it into an array before decoding. Any new JSON-consuming parser needs the same treatment.
- Minimum supported restic version is 0.15.0 (README); the committed test binaries cover 0.14–0.18.

## Gotchas

- `src/App/Commands.php` imports `MuckiRestic\Test\TestData` and `MuckiRestic\Test\TestHelper` and hardcodes
  `./bin/restic_0.17.3_linux_386`. The console app therefore only works from a dev checkout
  (`composer install`, not `--no-dev`) — it is a manual test harness, not a shipped CLI.
- `composer.json` carries an explicit `"version"` field. Releases require bumping it there (recent history shows this
  is done in its own commit).
- `BackupFactory::runUnlockCommand()` / `runPruneCommand()` are marked `@deprecated` — they are moving to
  `ManageFactory`. Prefer `Manage` for maintenance operations in new code.
- `Configuration::setTag()` appends to `$tags` (it does not set `$tag`); `getTag()` returns the unused `$tag`
  property. Use `setTags()`/`getTags()` when you need the real list.
- `AmazonS3` implementations call `setRepositoryPath('/')` internally and use `awsS3Endpoint` as the repository
  argument; `Backup\AmazonS3::removeOldRepository()` uses `aws/aws-sdk-php` directly to empty the bucket on
  `createRepository(true)` — that is a destructive delete-all of the bucket contents.
- PHPStan runs at level 8 with `treatPhpDocTypesAsCertain: false` over `src` only; the reflection-based property
  copying and array shapes mean new code needs complete `@param`/`@return` array annotations.
