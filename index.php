<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

use Router\Router;

$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"];

$router = new Router();

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $router->fetchRouteInfo($requestMethod, $uri);
$router->trigRequest($routeInfo, $uri);