<?php

namespace Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Utility\HttpHelper;
use function FastRoute\simpleDispatcher;

class Router
{
    private const CREATE_JWT = ['method' => 'POST', 'uri' => '/token', 'request' => 'createToken'];
    private const AUTHENTICATE_USER = ['method' => 'POST', 'uri' => '/login', 'request' => 'authenticateUser'];
    private const GET_USER_BY_ID = ['method' => 'GET', 'uri' => '/users/{id}', 'request' => 'getUserById'];
    private const GET_USER_BY_EMAIL = ['method' => 'GET', 'uri' => '/users/{email}', 'request' => 'getUserByEmail'];
    private const ADD_USER = ['method' => 'POST', 'uri' => '/users', 'request' => 'addUser'];
    private const UPDATE_USER = ['method' => 'PUT', 'uri' => '/users/{email}', 'request' => 'updateUser'];
    private const DELETE_USER = ['method' => 'DELETE', 'uri' => '/users/{email}', 'request' => 'deleteUser'];


    private Dispatcher $dispatcher;

    public function __construct()
    {
        $this->dispatcher = $this->addRoute();
    }


    private function addRoute(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $r) {
            $r->addRoute(self::CREATE_JWT['method'],
                self::CREATE_JWT['uri'],
                self::CREATE_JWT['request']);
            $r->addRoute(self::AUTHENTICATE_USER['method'],
                self::AUTHENTICATE_USER['uri'],
                self::AUTHENTICATE_USER['request']);
            $r->addRoute(self::GET_USER_BY_ID['method'],
                self::GET_USER_BY_ID['uri'],
                self::GET_USER_BY_ID['request']);
            $r->addRoute(self::GET_USER_BY_EMAIL['method'],
                self::GET_USER_BY_EMAIL['uri'],
                self::GET_USER_BY_EMAIL['request']);
            $r->addRoute(self::ADD_USER['method'],
                self::ADD_USER['uri'],
                self::ADD_USER['request']);
            $r->addRoute(self::UPDATE_USER['method'],
                self::UPDATE_USER['uri'],
                self::UPDATE_USER['request']);
            $r->addRoute(self::DELETE_USER['method'],
                self::DELETE_USER['uri'],
                self::DELETE_USER['request']);
        });
    }

    public function dispatch(string $requestMethod, string $route): array
    {
        return $this->dispatcher->dispatch($requestMethod, $route);
    }

    public function trigRequest(array $routeInfo, string $route, array $controllers): void
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
                $basePath = $route == '/token' || $route == "/login" ? ['', 'users'] : explode("/", $route);
                $controller = $controllers[$basePath[1]];
                call_user_func_array([$controller, $handler], $vars);
                break;
            default:
                HttpHelper::setResponse(400, 'Unexpected Error', true);
        }
    }
}