<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

use Service\Router;

// Récupération de la méthode et de l'URI
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"];

// allow CORS
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 1000');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
    }
    exit(0);
}

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