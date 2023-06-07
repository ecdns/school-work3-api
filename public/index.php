<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

use Service\Http;
use Service\Router;

// Récupération du conteneur de dépendances
$container = require '../config/di.php';

// Récupération du service Http
$http = $container->get(Http::class);

// Récupération de la méthode et de l'URI
$requestMethod = $http->getMethod();
$uri = $http->getUri();

// Autorisation des requêtes CORS
$http->allowCors();

// Suppression des paramètres GET dans l'URI si présents (ex: /users/1?name=John => /users/1)
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

// Décodage de l'URI
$uri = rawurldecode($uri);

// Création du routeur
$router = $container->get(Router::class);

// Récupération des informations de la route correspondant à la méthode et à l'URI et déclenchement de la requête
$requestInfo = $router->fetchRequestInfo($requestMethod, $uri);
$router->trigResponse($requestInfo);