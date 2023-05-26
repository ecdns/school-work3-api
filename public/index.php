<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

use Service\Router;

// Récupération de la méthode et de l'URI
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"];

// allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: *");
header("Content-Security-Policy: default-src '*'");
header("X-Content-Security-Policy: default-src '*'");

// Suppression des paramètres GET dans l'URI si présents (ex: /users/1?name=John => /users/1)
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

// Récupération du conteneur de dépendances
$container = require '../config/di.php';

// Création du routeur
$router = $container->get(Router::class);

// Décodage de l'URI
$uri = rawurldecode($uri);

// Récupération des informations de la route correspondant à la méthode et à l'URI et déclenchement de la requête
$requestInfo = $router->fetchRequestInfo($requestMethod, $uri);
$router->trigRequest($requestInfo);