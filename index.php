<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

use Dotenv\Dotenv;
use Router\Router;
use Service\DbManager;
use Service\Http;

// Récupération de la méthode et de l'URI
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"];

$dotenv = Dotenv::createImmutable(__DIR__ );
$dotenv->load();

// Récupération des informations de connexion à la base de données
$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbPort = $_ENV['DB_PORT'];

$entitiesPath = [__DIR__ . '../src/Entity'];
$isDevMode = true;

// Création de la connexion à la base de données
$dbManager = new DbManager($dbHost, $dbName, $dbUser, $dbPassword, $dbPort, $entitiesPath, $isDevMode);

try {
    $connexion = $dbManager->getConnexion();
} catch (Exception $e) {
    Http::sendStatusResponse(500, 'Internal Error');
    exit(1);
}

try {
    $entityManager = $dbManager->getEntityManager($connexion);
} catch (Exception $e) {
    Http::sendStatusResponse(500, 'Internal Error');
    exit(1);
}

// Création du routeur
$router = new Router();

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Lancement de la requête
$routeInfo = $router->fetchRouteInfo($requestMethod, $uri);
$router->trigRequest($routeInfo, $uri, $entityManager);