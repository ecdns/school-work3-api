<?php

namespace Router;

use Controller\CompanyController;
use Controller\CompanySettingsController;
use Controller\LicenseController;
use Controller\RoleController;
use Controller\UserController;
use Doctrine\ORM\EntityManager;
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

            // user routes
            $r->addRoute('POST', '/user/login', 'loginUser');
            $r->addRoute('POST', '/user', 'addUser');
            $r->addRoute('GET', '/user/{id:\d+}', 'getUserById');
            $r->addRoute('PUT', '/user/{id:\d+}', 'updateUser');
            $r->addRoute('DELETE', '/user/{id:\d+}', 'deleteUser');

            // company routes
            $r->addRoute('POST', '/company', 'addCompany');
            $r->addRoute('GET', '/company/{id:\d+}', 'getCompanyById');
            $r->addRoute('PUT', '/company/{id:\d+}', 'updateCompany');
            $r->addRoute('DELETE', '/company/{id:\d+}', 'deleteCompany');

            // company settings routes
            $r->addRoute('POST', '/company-settings', 'addCompanySettings');
            $r->addRoute('GET', '/company-settings/{id:\d+}', 'getCompanySettingsById');
            $r->addRoute('PUT', '/company-settings/{id:\d+}', 'updateCompanySettings');
            $r->addRoute('DELETE', '/company-settings/{id:\d+}', 'deleteCompanySettings');

            // license routes
            $r->addRoute('POST', '/license', 'addLicense');
            $r->addRoute('GET', '/license/{id:\d+}', 'getLicenseById');
            $r->addRoute('PUT', '/license/{id:\d+}', 'updateLicense');
            $r->addRoute('DELETE', '/license/{id:\d+}', 'deleteLicense');

            // role routes
            $r->addRoute('POST', '/role', 'addRole');
            $r->addRoute('GET', '/role/{id:\d+}', 'getRoleById');
            $r->addRoute('PUT', '/role/{id:\d+}', 'updateRole');
            $r->addRoute('DELETE', '/role/{id:\d+}', 'deleteRole');
        });
    }

    public function fetchRouteInfo(string $requestMethod, string $route): array
    {
        return $this->dispatcher->dispatch($requestMethod, $route);
    }

    public function setController(string $route, EntityManager $entityManager): object
    {

        $uri = "/" . explode('/', $route)[1];

        switch ($uri) {

            case "/user":
                return new UserController($entityManager);
            case "/license":
                return new LicenseController($entityManager);
            case "/company":
                return new CompanyController($entityManager);
            case "/role":
                return new RoleController($entityManager);
            case "/company-settings":
                return new CompanySettingsController($entityManager);
            default:
                HttpHelper::setResponse(500, 'Internal Error', true);
                exit(1);
        }

    }

    public function trigRequest(array $routeInfo, string $route, EntityManager $entityManager): void
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
                $controller = $this->setController($route, $entityManager);
                call_user_func_array([$controller, $handler], $vars);
                break;
            default:
                HttpHelper::setResponse(400, 'Unexpected Error', true);
        }
    }
}