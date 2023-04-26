<?php

declare(strict_types=1);

require "vendor/autoload.php";

use Dotenv\Dotenv as Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbPort = $_ENV['DB_PORT'];

$paths = [__DIR__ . '/src/Entity'];
$isDevMode = true;

return [
    'driver'   => 'pdo_mysql',
    'user'     => $dbUser,
    'host'     => $dbHost,
    'port'     => $dbPort,
    'password' => $dbPassword,
    'dbname'   => $dbName,
];
