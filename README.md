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