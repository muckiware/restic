# muckiware/restic
PHP client for [restic](https://github.com/restic/restic) backup tool. This library provides a simple way to create and manage backups with restic. It uses repositories as storage for backups.
<div align="center">

[![Latest Stable Version](http://poser.pugx.org/muckiware/restic/v)](https://packagist.org/packages/muckiware/restic)
[![Total Downloads](http://poser.pugx.org/muckiware/restic/downloads)](https://packagist.org/packages/muckiware/restic)
[![PHP Version Require](http://poser.pugx.org/muckiware/restic/require/php)](https://packagist.org/packages/muckiware/restic)
[![Dependents](http://poser.pugx.org/muckiware/restic/dependents)](https://packagist.org/packages/muckiware/restic)
[![License](https://img.shields.io/github/license/shopware/shopware.svg)](https://github.com/shopware/shopware/blob/trunk/LICENSE)

</div>

# Requirements
- PHP 8.1 or higher
- Composer
- Restic as binary or installed on the system, see https://restic.readthedocs.io/en/stable/020_installation.html
- Restic version 0.15.0 or higher
- AWS account with S3 bucket, if you want to use Amazon S3 as external storage for the backup repository
# Installation
```bash
composer require muckiware/restic
```
# Usage
How to use the library. This php client interacts with the restic binary to create, manage and restore backups in and of a repository. The first step is always to create a backup repository as storage for the backup data. After that, you can create backups in this repository and check the backup data. And at least if its necessary, you can restore the backup data.

## Location of backup repository
The backup repository can be located on the local file system, or on an external S3 storage. Currently this library supports AWS / AmazonS3 as external storage for the backup repository. More details about the Amazon Bucket configuration in the restic documentation https://restic.readthedocs.io/en/latest/080_examples.html#setting-up-restic-with-amazon-s3
## Create a new backup repository
You will need first the backup object of the library, for to use the **createRepository** method. Import this class with `use MuckiRestic\Library\Backup;`. The Backup-class has a static `create`-method for to get the Backup object, like this `$backupClient = Backup::create();`. With this create, you have access to all the Backup methods. The `$backupClient->createRepository()` method initialize a new repository and  need the required parameters _password_ and the _repositoryPath_. The repositoryPath is where the backup data will be stored and the password is used to encrypt the backup data. It's required for all operations on the repository. It has to be set by the two setting methods `$backupClient->setRepositoryPassword('1234')` and `$backupClient->setRepositoryPath('./path_to_repository')`<br>
Optionally you can set the path for the restic binary, with `$backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386')`. This is necessary if the restic binary is not installed in the local system. 

The method `createRepository()` returns the object `ResultEntity`.

The method `getOutput` of the object `ResultEntity` returns the output of the restic command. If an error occurs, an exception will be thrown.
### Example for local repository
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
            $backupClient->setRepositoryPassword('12345%ASDEee'); //required
            $backupClient->setRepositoryPath('./path_to_repository'); //required

            echo $backupClient->createRepository()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
### Example for Amazon S3 storage
```php
<?php declare(strict_types=1);

use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Library\Backup;

class BackupService
{
    public function createRepository(): void
    {
        try {
        
            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $backupClient->setRepositoryPassword('12345%ASDEee'); //required
            $backupClient->setAwsAccessKeyId('AABBCCDDYUI4T123WIZY'); //required
            $backupClient->setAwsSecretAccessKey('xLqWLrN1yfrJ+r2zlnpoMY3eDXdHmdnne8T+Y2XZ'); //required
            $backupClient->setAwsRegion('eu-central-1'); //required
            $backupClient->setAwsS3Endpoint('s3:https://s3.amazonaws.com/my-restic-bucket'); //required
            $backupClient->setAwsS3BucketName('my-restic-bucket'); //required

            echo $backupClient->createRepository(RepositoryLocationTypes::AWSS3)->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Create a backup
Next step, create a backup into the repository by using the method `$backupClient->createBackup()`. Also, this method will returns the object `ResultEntity`. The backup path is required for the backup operation. It has to be set by the method `$backupClient->setBackupPath('./path_to_backup_folder')`.<br>
Every backup process creates a new **snapshot** of the backup data in the repository. These **snapshots** are represented by an individually hash string.
### Example for local repository
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
            $backupClient->setRepositoryPassword('12345%ASDEee'); //required
            $backupClient->setRepositoryPath('./path_to_repository'); //required
            $backupClient->setBackupPath('./path_to_backup_folder'); //required
            
            echo $backupClient->createBackup()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
### Example for Amazon S3 storage
```php
<?php declare(strict_types=1);

use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Library\Backup;

class BackupService
{
    public function createBackup(): void
    {
        try {
        
            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $backupClient->setRepositoryPassword('12345%ASDEee'); //required
            $backupClient->setBackupPath('./path_to_backup_folder'); //required
            $backupClient->setAwsAccessKeyId('AABBCCDDYUI4T123WIZY'); //required
            $backupClient->setAwsSecretAccessKey('xLqWLrN1yfrJ+r2zlnpoMY3eDXdHmdnne8T+Y2XZ'); //required
            $backupClient->setAwsRegion('eu-central-1'); //required
            $backupClient->setAwsS3Endpoint('s3:https://s3.amazonaws.com/my-restic-bucket'); //required
            $backupClient->setAwsS3BucketName('my-restic-bucket'); //required
            
            echo $backupClient->createBackup(RepositoryLocationTypes::AWSS3)->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Check the backup
After the backup process, it makes sense to check the backup data. The method `$backupClient->checkBackup()` will return the object `ResultEntity`. The method `getOutput` of the object `ResultEntity` returns the output of the restic command simple as string. If an error occurs, an exception will be thrown.
### Example for local repository
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
            $backupClient->setRepositoryPassword('1234'); //required
            $backupClient->setRepositoryPath('./path_to_repository'); //required
            
            echo $backupClient->checkBackup()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
### Example for Amazon S3 storage
```php
<?php declare(strict_types=1);

use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Library\Backup;

class BackupService
{
    public function createBackup(): void
    {
        try {
        
            $backupClient = Backup::create();
            $backupClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $backupClient->setRepositoryPassword('1234'); //required
            $backupClient->setAwsAccessKeyId('AABBCCDDYUI4T123WIZY'); //required
            $backupClient->setAwsSecretAccessKey('xLqWLrN1yfrJ+r2zlnpoMY3eDXdHmdnne8T+Y2XZ'); //required
            $backupClient->setAwsRegion('eu-central-1'); //required
            $backupClient->setAwsS3Endpoint('s3:https://s3.amazonaws.com/my-restic-bucket'); //required
            $backupClient->setAwsS3BucketName('my-restic-bucket'); //required
            
            echo $backupClient->checkBackup(RepositoryLocationTypes::AWSS3)->getOutput();
        
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
### Example for local repository
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
            $manageClient->setRepositoryPassword('1234'); //required
            $manageClient->setRepositoryPath('./path_to_repository'); //required
            
            echo $manageClient->getSnapshots()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
### Example for Amazon S3 storage
```php
<?php declare(strict_types=1);

use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Library\Manage;

class ManageService
{
    public function getSnapshotList(): void
    {
        try {
        
            $manageClient = Manage::create();
            $manageClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $manageClient->setRepositoryPassword('1234'); //required
            $manageClient->setAwsAccessKeyId('AABBCCDDYUI4T123WIZY'); //required
            $manageClient->setAwsSecretAccessKey('xLqWLrN1yfrJ+r2zlnpoMY3eDXdHmdnne8T+Y2XZ'); //required
            $manageClient->setAwsRegion('eu-central-1'); //required
            $manageClient->setAwsS3Endpoint('s3:https://s3.amazonaws.com/my-restic-bucket'); //required
            $manageClient->setAwsS3BucketName('my-restic-bucket'); //required
            
            echo $manageClient->getSnapshots(RepositoryLocationTypes::AWSS3)->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Remove snapshot by id
You can remove a snapshot of a repository by id with the method `$manageClient->getSnapshots()`. The method `getSnapshots` returns the object `ResultEntity`. The method `getOutput` of the object `ResultEntity` returns the output of the restic command simple as string. If an error occurs, an exception will be thrown.
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
            $manageClient->setRepositoryPassword('1234'); //required
            $manageClient->setRepositoryPath('./path_to_repository'); //required
            $manageClient->setSnapshotId('snapshot_id'); //required
            
            echo $manageClient->removeSnapshotById()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
### Example for Amazon S3 storage
```php
<?php declare(strict_types=1);

use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Library\Manage;

class ManageService
{
    public function getSnapshotList(): void
    {
        try {
        
            $manageClient = Manage::create();
            $manageClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $manageClient->setRepositoryPassword('1234'); //required
            $manageClient->setSnapshotId('snapshot_id'); //required
            $manageClient->setAwsAccessKeyId('AABBCCDDYUI4T123WIZY'); //required
            $manageClient->setAwsSecretAccessKey('xLqWLrN1yfrJ+r2zlnpoMY3eDXdHmdnne8T+Y2XZ'); //required
            $manageClient->setAwsRegion('eu-central-1'); //required
            $manageClient->setAwsS3Endpoint('s3:https://s3.amazonaws.com/my-restic-bucket'); //required
            $manageClient->setAwsS3BucketName('my-restic-bucket'); //required
            
            echo $manageClient->removeSnapshotById(RepositoryLocationTypes::AWSS3)->getOutput();
        
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
### Example for local repository
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
            $manageClient->setRepositoryPassword('1234'); //required
            $manageClient->setRepositoryPath('./path_to_repository'); //required
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
### Example for Amazon S3 storage
```php
<?php declare(strict_types=1);

use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Library\Manage;

class ManageService
{
    public function removeOldSnapshots(): void
    {
        try {
        
            $manageClient = Manage::create();
            $manageClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $manageClient->setRepositoryPassword('1234'); //required
            $manageClient->setKeepDaily(1); //optional
            $manageClient->setKeepWeekly(2); //optional
            $manageClient->setKeepMonthly(4); //optional
            $manageClient->setKeepYearly(5); //optional
            $manageClient->setAwsAccessKeyId('AABBCCDDYUI4T123WIZY'); //required
            $manageClient->setAwsSecretAccessKey('xLqWLrN1yfrJ+r2zlnpoMY3eDXdHmdnne8T+Y2XZ'); //required
            $manageClient->setAwsRegion('eu-central-1'); //required
            $manageClient->setAwsS3Endpoint('s3:https://s3.amazonaws.com/my-restic-bucket'); //required
            $manageClient->setAwsS3BucketName('my-restic-bucket'); //required
            
            echo $manageClient->removeSnapshots(RepositoryLocationTypes::AWSS3)->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```
## Restore a backup
You can restore a backup from a repository with the method `$restoreClient->restoreBackup()`. The method `restoreBackup` returns also the object `ResultEntity`. The method `getOutput` of the object `ResultEntity` returns the output of the restic command simple as string. If an error occurs, an exception will be thrown.<br>
As default the method `restoreBackup` will restore the latest snapshot. Optionally you can set the snapshot hash with the method `setRestoreItem(string $snapshotHash)`. The snapshot hash you can get from the method `getSnapshots`.<br>
### Example for local repository
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
            $restoreClient->setRepositoryPassword('1234'); //required
            $restoreClient->setRepositoryPath('./path_to_repository'); //required
            $restoreClient->setRestoreTarget('./path_to_restore_folder'); //required
            $restoreClient->setRestoreItem('snapshot_hash'); //optional
            
            echo $restoreClient->createRestore()->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```

### Example for Amazon S3 storage
```php
<?php declare(strict_types=1);

use MuckiRestic\Core\RepositoryLocationTypes;
use MuckiRestic\Library\Restore;

class RestoreService
{
    public function createRestore(): void
    {
        try {
        
            $restoreClient = Restore::create();
            $restoreClient->setBinaryPath('./bin/restic_0.17.3_linux_386'); //optional
            $restoreClient->setRepositoryPassword('1234');
            $restoreClient->setRestoreTarget('./path_to_restore_folder');
            $restoreClient->setRestoreItem('snapshot_hash'); //optional
            $restoreClient->setAwsAccessKeyId('AABBCCDDYUI4T123WIZY'); //required
            $restoreClient->setAwsSecretAccessKey('xLqWLrN1yfrJ+r2zlnpoMY3eDXdHmdnne8T+Y2XZ'); //required
            $restoreClient->setAwsRegion('eu-central-1'); //required
            $restoreClient->setAwsS3Endpoint('s3:https://s3.amazonaws.com/my-restic-bucket'); //required
            $restoreClient->setAwsS3BucketName('my-restic-bucket'); //required
            
            echo $restoreClient->createRestore(RepositoryLocationTypes::AWSS3)->getOutput();
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
```

## Use as cli app
Checkout the App folder for to run as cli command
```shell
bin/console muwa:restic:client --help
```
Get version of restic binary
```shell
bin/console muwa:restic:client --Version
```
### Init a new backup repository
```shell
bin/console muwa:restic:client --Init <Repository> <Password>
```
- _Repository_ - Free of choice, where the backup data will be stored.<br>
- _Password_ - Password for the backup repository, which is used to encrypt the backup data. It's required for all operations on the repository.
  Init a new backup repository
### Create a backup
```shell
bin/console muwa:restic:client --Backup <Repository> <Password> <Backup>
```
- _Repository_ - Path to the backup repository<br>
- _Password_ - Password for the backup repository.
- _Backup_ - Path to data which should be backed up. This can be a single file or a folder. If the path is a folder, all files and subfolders will be backed up.
### Check the backup, by getting a list of all snapshots
```shell
bin/console muwa:restic:client --Snapshots <Repository> <Password>
```
- _Repository_ - Path to the backup repository<br>
- _Password_ - Password for the backup repository.
### Remove specific snapshot
```shell
bin/console muwa:restic:client --Snapshots <Repository> <Password> -r --snapshotId <SnapshotId>
```
- _Repository_ - Path to the backup repository<br>
- _Password_ - Password for the backup repository.
### Remove old snapshots
```shell
bin/console muwa:restic:client --Forget <Repository> <Password>
```
- _Repository_ - Path to the backup repository<br>
- _Password_ - Password for the backup repository.
# Testing
Run phpunit tests
```shell
./vendor/bin/phpunit --configuration=phpunit.xml
./vendor/bin/phpunit --configuration=phpunit_without_integration.xml
```
Run phpstan tests
```shell
composer run-script phpstan
```
# License
MIT License (MIT). Please see LICENSE File for more information.

# Notice
If you run muckiware/restic on a ddev/Docker environment, you could get a **read/error** of the backup files. In this case, check the mutagen status, and enable the mutagen sync. This library is only checked on a Linux and MacOS environment. All components are also available for Windows, but no warranty that is also working on a Windows environment.
