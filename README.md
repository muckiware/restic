# muckiware/restic
PHP client for restic backup tool

# Requirements
- PHP 8.1 or higher
- Composer
- Restic as binary or installed on the system, see https://restic.readthedocs.io/en/stable/020_installation.html
# Installation
```bash
composer require muckiware/restic
```
# Usage
## Create a new backup repository
```php
<?php declare(strict_types=1);

use MuckiRestic\Library\Backup;
use MuckiRestic\Exception\InvalidConfigurationException;

try {

    $backupClient = Backup::create();
    $backupClient->setBinaryPath('/var/www/html/bin/restic_0.17.3_linux_386');
    $backupClient->setRepositoryPassword('1234');
    $backupClient->setRepositoryPath('./Repository');
    $backupClient->createRepository();

} catch (\Exception $e) {
    echo $e->getMessage();
}
```