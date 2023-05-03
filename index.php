<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

use Dotenv\Dotenv;
use Router\Router;
use Service\DbManager;
use Service\Request;

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
    Request::handleErrorAndQuit(500, $e);
}

try {
    $entityManager = $dbManager->getEntityManager($connexion);
} catch (Exception $e) {
    Request::handleErrorAndQuit(500, $e);
}

// Création du routeur
$router = new Router();

// Suppression des paramètres GET dans l'URI si présents (ex: /users/1?name=John => /users/1)
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

// Décodage de l'URI
$uri = rawurldecode($uri);

// Récupération des informations de la route correspondant à la méthode et à l'URI et déclenchement de la requête
$routeInfo = $router->fetchRouteInfo($requestMethod, $uri);
$router->trigRequest($routeInfo, $uri, $entityManager);