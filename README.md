# muckiware/restic
php client for restic backup tool

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