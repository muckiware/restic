<?php declare(strict_types=1);
use Symfony\Component\Dotenv\Dotenv;
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
}
require __DIR__ . '/../vendor/autoload.php';
