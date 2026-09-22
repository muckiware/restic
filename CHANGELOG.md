# Changelog

All notable changes to this project are documented in this file.

## [Unreleased]

### Added

- **Configurable process timeouts.** `setProcessTimeout()` / `getProcessTimeout()` set the
  wall clock limit for a single restic process, `setProcessIdleTimeout()` /
  `getProcessIdleTimeout()` abort a process that produces no output for that long. Both
  accept an integer or a float, and `null` removes the limit. The idle timeout is off by
  default. The setting lives on the shared configuration base, so it applies to `Backup`,
  `Manage` and `Restore` alike.
- Both setters reject `0` and negative values with an `InvalidConfigurationException`.
  Symfony reads a timeout of `0` as "no limit", and an integer field left empty in a
  configuration UI yields exactly `0` — without the check, a forgotten setting would
  silently produce a backup process that can never time out. Use `null` when that is what
  you mean.

### Changed

- **The default process timeout is now 3600 seconds, up from a hard-coded 1000.** A first
  backup, or a `prune` on a repository that has grown over time, regularly exceeds 16
  minutes; those runs were aborted with a `ProcessTimedOutException` and counted as
  failures. Anything that completed within the old limit is unaffected. Raise it further
  with `setProcessTimeout()` for large repositories.

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
- **BREAKING** Configuration setters (`repositoryPath`, `backupPath`, `binaryPath`,
  `tags` and others, 13 in total) now reject values starting with a dash and throw
  `InvalidConfigurationException`. A value a setter previously accepted silently can
  now throw at configuration time.
- **BREAKING** `ResultEntity::getCommandLine()` now returns the shell-escaped string
  produced by Symfony's `Process::getCommandLine()` for an array commandline (every
  token single-quoted), e.g. `'/usr/bin/restic' '--repo=/srv/repo' 'backup'` instead of
  `/usr/bin/restic --repo=/srv/repo backup`. Any consumer logging, displaying or
  re-parsing this string is affected.

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
3. If you log, display or re-parse `ResultEntity::getCommandLine()`, note that it is now
   a shell-escaped, single-quoted string rather than the plain command line.

The setter guards validate the *shape* of a value (it must not start with a dash), not
whether it is trustworthy. A value like `rest:http://attacker/` or `s3:https://attacker/`
passes the guard but redirects the entire backup to an attacker-controlled server. If a
configuration value originates from untrusted input (e.g. an HTTP request), whitelist
the allowed scheme or prefix yourself before passing it in.
