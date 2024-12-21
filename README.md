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
How to use the library. This php client interacts with the restic binary to create, manage and restore backups in and of a repository. The first step is always to create a backup repository as storage for the backup data. After that, you can create backups in this repository and check the backup data. And at least if its necessary, you can restore the backup data.

## Create a new backup repository
You will need first the backup object of the library, for to use the **createRepository** method. Import this class with `use MuckiRestic\Library\Backup;`. The Backup-class has a static `create`-method for to get the Backup object, like this `$backupClient = Backup::create();`. With this create, you have access to all the Backup methods. The `$backupClient->createRepository()` method initialize a new repository and  need the required parameters _password_ and the _repositoryPath_. The repositoryPath is where the backup data will be stored and the password is used to encrypt the backup data. It's required for all operations on the repository. It has to be set by the two setting methods `$backupClient->setRepositoryPassword('1234')` and `$backupClient->setRepositoryPath('./path_to_repository')`<br>
Optionally you can set the path for the restic binary, with `$backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386')`. This is necessary if the restic binary is not installed in the local system. 

The method `createRepository()` returns the object `ResultEntity`.

The method `getOutput` of the object `ResultEntity` returns the output of the restic command. If an error occurs, an exception will be thrown.
### Example
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Backup;

class BackupService
{
    public function createRepository(): void
    {
        try {
        
            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $backupClient->setRepositoryPassword('1234');
            $backupClient->setRepositoryPath('./path_to_repository');

            echo $backupClient->createRepository()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Create a backup
Next step, create a backup into the repository by using the method `$backupClient->createBackup()`. Also, this method will returns the object `ResultEntity`. The backup path is required for the backup operation. It has to be set by the method `$backupClient->setBackupPath('./path_to_backup_folder')`.<br>
Every backup process creates a new **snapshot** of the backup data in the repository. These **snapshots** are represented by an individually hash string.
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
            $backupClient->setRepositoryPath('./path_to_repository');
            $backupClient->setBackupPath('./path_to_backup_folder'););
            
            echo $backupClient->createBackup()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Check the backup
After the backup process, it makes sense to check the backup data. The method `$backupClient->checkBackup()` will return the object `ResultEntity`. The method `getOutput` of the object `ResultEntity` returns the output of the restic command simple as string. If an error occurs, an exception will be thrown.
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
            $backupClient->setRepositoryPath('./path_to_repository');
            
            echo $backupClient->checkBackup()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Manage backups
The library provides methods for to manage backups. Import this class with `use MuckiRestic\Library\Manage;`.
## Get list of snapshots
You can get list of all snapshots of a repository with the method `$manageClient->getSnapshots()`. The method `getSnapshots` returns the object `ResultEntity`. The method `getOutput` of the object `ResultEntity` returns the output of the restic command simple as string. If an error occurs, an exception will be thrown.
### Example
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Manage;

class ManageService
{
    public function getSnapshotList(): void
    {
        try {
        
            $manageClient = Manage::create();
            $manageClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $manageClient->setRepositoryPassword('1234');
            $manageClient->setRepositoryPath('./path_to_repository');
            
            echo $manageClient->getSnapshots()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Remove old snapshots
You can remove old snapshots of a repository with the method `$manageClient->removeSnapshots()`. This is kind a like a cleanup run for the repository. The method `removeSnapshots` returns as always the object `ResultEntity`. The method `getOutput` of the object `ResultEntity` returns the output of the restic command simple as string. If an error occurs, an exception will be thrown.<br>
This cleanup run needs to be setup with the keep-parameters, which defined the number of daily, weekly, monthly and yearly snapshots to keep. The method `setKeepDaily(int $keepDaily)`, `setKeepWeekly(int $keepWeekly)`, `setKeepMonthly(int $keepMonthly)` and `setKeepYearly(int $keepYearly)` are used to set the keep-parameters.
### default keep-parameters
| Parameter | value |
|:----------|:------|
| $keepDaily    | 7     |
| $keepWeekly    | 5     |
| $keepMonthly    | 12    |
| $keepYearly    | 75    |
More details about the keep-parameters you can find in the restic documentation https://restic.readthedocs.io/en/latest/060_forget.html#removing-snapshots-according-to-a-policy
### Example
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Manage;

class ManageService
{
    public function removeOldSnapshots(): void
    {
        try {
        
            $manageClient = Manage::create();
            $manageClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $manageClient->setRepositoryPassword('1234');
            $manageClient->setRepositoryPath('./path_to_repository');
            $manageClient->setKeepDaily(1); //optional
            $manageClient->setKeepWeekly(2); //optional
            $manageClient->setKeepMonthly(4); //optional
            $manageClient->setKeepYearly(5); //optional
            
            echo $manageClient->removeSnapshots()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Restore a backup
You can restore a backup from a repository with the method `$restoreClient->restoreBackup()`. The method `restoreBackup` returns also the object `ResultEntity`. The method `getOutput` of the object `ResultEntity` returns the output of the restic command simple as string. If an error occurs, an exception will be thrown.<br>
As default the method `restoreBackup` will restore the latest snapshot. Optionally you can set the snapshot hash with the method `setRestoreItem(string $snapshotHash)`. The snapshot hash you can get from the method `getSnapshots`.<br>
### Example
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Restore;

class RestoreService
{
    public function createRestore(): void
    {
        try {
        
            $restoreClient = Restore::create();
            $restoreClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $restoreClient->setRepositoryPassword('1234');
            $restoreClient->setRepositoryPath('./path_to_repository');
            $restoreClient->setRestoreTarget('./path_to_restore_folder');
            $restoreClient->setRestoreItem('snapshot_hash'); //optional
            
            echo $restoreClient->createRestore()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Class `MuckiRestic\Entity\Result\ResultEntity`
The `ResultEntity` class is part of the `MuckiRestic` library and extends the `DataSet` class. It represents the result of a backup operation and contains various properties and methods to access and manipulate the result data.
### Properties
- `protected ?SnapshotsResultEntity $snapshots`: Stores snapshot results.
- `protected ?FilesDirsResultEntity $files`: Stores file results.
- `protected ?FilesDirsResultEntity $directories`: Stores directory results.
- `protected ?array $processed`: Stores processed data.
- `protected ?string $commandLine`: Stores the command line used for the operation.
- `protected ?string $status`: Stores the status of the operation.
- `protected ?float $duration`: Stores the duration of the operation.
- `protected ?float $startTime`: Stores the start time of the operation.
- `protected ?float $endTime`: Stores the end time of the operation.
- `protected ?string $output`: Stores the output of the operation.
- `protected mixed $resticResponse`: Stores the response from the restic command.

### Methods
#### `getSnapshots(): ?SnapshotsResultEntity`
Returns the snapshot results.

#### `setSnapshots(SnapshotsResultEntity $snapshots): void`
Sets the snapshot results.

#### `getFiles(): ?FilesDirsResultEntity`
Returns the file results.

#### `setFiles(FilesDirsResultEntity $files): void`
Sets the file results.

#### `getDirectories(): ?FilesDirsResultEntity`
Returns the directory results.

#### `setDirectories(FilesDirsResultEntity $directories): void`
Sets the directory results.

#### `getProcessed(): ?array`
Returns the processed data.

#### `setProcessed(array $processed): void`
Sets the processed data.

#### `addProcessed(string $processed): void`
Adds a processed data entry.

#### `getCommandLine(): ?string`
Returns the command line used for the operation.

#### `setCommandLine(?string $commandLine): void`
Sets the command line used for the operation.

#### `getStatus(): ?string`
Returns the status of the operation.

#### `setStatus(?string $status): void`
Sets the status of the operation.

#### `getStartTime(): ?float`
Returns the start time of the operation.

#### `setStartTime(?float $startTime): void`
Sets the start time of the operation.

#### `getEndTime(): ?float`
Returns the end time of the operation.

#### `setEndTime(?float $endTime): void`
Sets the end time of the operation.

#### `getDuration(): ?float`
Calculates and returns the duration of the operation.

#### `setDuration(): void`
Calculates and sets the duration of the operation.

#### `getOutput(): ?string`
Returns the output of the operation.

#### `setOutput(?string $output): void`
Sets the output of the operation.

#### `getResticResponse(): mixed`
Returns the response from the restic command.

#### `setResticResponse(mixed $resticResponse): void`
Sets the response from the restic command.

## Example as cli app
Checkout the App folder for to run as cli command
```shell
bin/console muwa:restic:client --help
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
If you run muckiware/restic on a ddev/Docker environment, you could get a **read/error** of the backup files. In this case, check the mutagen status, and enable the mutagen sync. This library is only checked on a Linux and MacOS environment. All components are also available for Windows, but no warranty that is also working on a Windows environment.
