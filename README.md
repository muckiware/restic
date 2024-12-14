# muckiware/restic
PHP client for restic backup tool. This library provides a simple way to create and manage backups with restic. It uses repositories as storage for backups.

# Requirements
- PHP 8.1 or higher
- Composer
- Restic as binary or installed on the system, see https://restic.readthedocs.io/en/stable/020_installation.html
# Installation
```bash
composer require muckiware/restic
```
# Usage
## Example as cli app
Checkout the App folder for to run as cli command
```shell
bin/console muwa:restic:client --help
```
## Commands
| Command                                                       | Required parameters settings                                                                                                                | Optional parameters settings                                                                                                                                                                                                                          | Desc                                           |
|:--------------------------------------------------------------|:--------------------------------------------------------------------------------------------------------------------------------------------|:------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:-----------------------------------------------|
| MuckiRestic\Library\Backup\create()->createRepository()       | MuckiRestic\Library\Backup\create()<br/>->setRepositoryPassword(string)<br/>->setRepositoryPath(_string_)                                   | MuckiRestic\Library\Backup\create()<br/>->setBinaryPath(string) **default: restic**                                                                                                                                                                   | Create a new backup repository                 |
| MuckiRestic\Library\Backup\create()->createBackup()           | MuckiRestic\Library\Backup\create()<br/>->setRepositoryPassword(string)<br/>->setRepositoryPath(_string_)<br/>->setBackupPath(_string_)     | MuckiRestic\Library\Backup\create()<br/>->setBinaryPath(string) **default: restic**<br/>->setSkipPrepareBackup(bool) **default: false**<br/>->setCompress(bool) **default: true**                                                                     | Create a new backup with backup path           |
| MuckiRestic\Library\Backup\create()->checkBackup()            | MuckiRestic\Library\Backup\create()<br/>->setRepositoryPassword(string)<br/>->setRepositoryPath(_string_)                                   | MuckiRestic\Library\Backup\create()<br/>->setBinaryPath(string) **default: restic**                                                                                                                                                                   | Checkup an existing repository                 |
| MuckiRestic\Library\Manage\create()->getSnapshots()           | MuckiRestic\Library\Manage\Manage()<br/>->setRepositoryPassword(string)<br/>->setRepositoryPath(_string_)                                   | MuckiRestic\Library\Manage\create()<br/>->setBinaryPath(string) **default: restic**                                                                                                                                                                   | Get a list of snapshots which in a repository  |
| MuckiRestic\Library\Manage\create()->executeForget()           | MuckiRestic\Library\Manage\create()<br/>->setRepositoryPassword(string)<br/>->setRepositoryPath(_string_)                                   | MuckiRestic\Library\Manage\create()<br/>->setBinaryPath(string) **default: restic**<br/>->setKeepDaily(int) **default: 7**<br/>->setKeepWeekly(int) **default: 5**<br/>->setKeepMonthly(int) **default: 12**<br/>->setKeepYearly(int) **default: 75** | Remove old snapshot from repository            |
| MuckiRestic\Library\Restore\create()->createRestore()          | MuckiRestic\Library\Restore\create()<br/>->setRepositoryPassword(string)<br/>->setRepositoryPath(_string_)<br/>->setRestoreTarget(_string_) | MuckiRestic\Library\Restore\create()<br/>->setBinaryPath(string) **default: restic**<br/>                                                                                                                                                             | Get all files of a specific or latest snapshop |


## Create a new backup repository
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Backup;

class BackupService
{
    public function createRepository(): void
    {
        try {
        
            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword('1234');
            $backupClient->setRepositoryPath('./Repository');
            $backupClient->createRepository()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```

## Create a backup
### Example
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Backup;

class BackupService
{
    public function createBackup(): void
    {
        try {
        
            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $backupClient->setRepositoryPassword('1234');
            $backupClient->setRepositoryPath('./testRep');
            $backupClient->setBackupPath('./test');
            
            echo $backupClient->createBackup()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```

## Check a backup
### Example
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Backup;

class BackupService
{
    public function createBackup(): void
    {
        try {
        
            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386');
            $backupClient->setRepositoryPassword('1234');
            $backupClient->setRepositoryPath('./testRep');
            
            echo $backupClient->checkBackup()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```

# Testing
Run phpunit tests
```shell
./vendor/bin/phpunit --configuration=phpunit.xml
```
Run phpstan tests
```shell
composer run-script phpstan
```
# License
MIT License (MIT). Please see LICENSE File for more information.

# Notice
If you run muckiware/restic on a ddev/Docker environment, you could get an read error of the backup files. In this case, check the mutagen status, and enable the mutagen sync.
