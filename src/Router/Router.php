<?php

namespace Router;

use Controller\UserController;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Service\HttpHelper;
use function FastRoute\simpleDispatcher;

class Router
{

    private Dispatcher $dispatcher;

    public function __construct()
    {
        $this->dispatcher = $this->addRoutes();
    }

    private function addRoutes(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $r) {

            $r->addRoute('GET', '/', 'test');

        });
    }

    public function fetchRouteInfo(string $requestMethod, string $route): array
    {
        return $this->dispatcher->dispatch($requestMethod, $route);
    }

    public function setController(string $route): object
    {

        $uri = "/" . explode('/', $route)[1];

        switch ($uri) {

            case "/user":
                return new UserController();
                break;
        }
    }

    public function trigRequest(array $routeInfo, string $route): void
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                HttpHelper::setResponse(404, 'Route Not Found', true);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                HttpHelper::setResponse(405, 'Method Not Allowed', true);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $controller = $this->setController($route);
                call_user_func_array([$controller, $handler], $vars);
                break;
            default:
                HttpHelper::setResponse(400, 'Unexpected Error', true);
        }
    }
}