<?php

namespace Router;

use Controller\CompanyController;
use Controller\CompanySettingsController;
use Controller\LicenseController;
use Controller\RoleController;
use Controller\UserController;
use Controller\UserSettingsController;
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
            $r->addRoute('GET', '/user', 'getUsers');
            $r->addRoute('GET', '/user/{id:\d+}', 'getUserById');
            $r->addRoute('PUT', '/user/{id:\d+}', 'updateUser');
            $r->addRoute('DELETE', '/user/{id:\d+}', 'deleteUser');

            // user settings routes
            $r->addRoute('POST', '/user-settings', 'addUserSettings');
            $r->addRoute('GET', '/user-settings/{id:\d+}', 'getUserSettingsById');
            $r->addRoute('PUT', '/user-settings/{id:\d+}', 'updateUserSettings');
            $r->addRoute('DELETE', '/user-settings/{id:\d+}', 'deleteUserSettings');

            // company routes
            $r->addRoute('POST', '/company', 'addCompany');
            $r->addRoute('GET', '/company', 'getCompanies');
            $r->addRoute('GET', '/company/{id:\d+}', 'getCompanyById');
            $r->addRoute('GET', '/company/{name}', 'getCompanyByName');
            $r->addRoute('PUT', '/company/{id:\d+}', 'updateCompany');
            $r->addRoute('DELETE', '/company/{id:\d+}', 'deleteCompany');

            // company settings routes
            $r->addRoute('POST', '/company-settings', 'addCompanySettings');
            $r->addRoute('GET', '/company-settings/{id:\d+}', 'getCompanySettingsById');
            $r->addRoute('PUT', '/company-settings/{id:\d+}', 'updateCompanySettings');
            $r->addRoute('DELETE', '/company-settings/{id:\d+}', 'deleteCompanySettings');

            // license routes
            $r->addRoute('POST', '/license', 'addLicense');
            $r->addRoute('GET', '/license', 'getLicenses');
            $r->addRoute('GET', '/license/{id:\d+}', 'getLicenseById');
            $r->addRoute('PUT', '/license/{id:\d+}', 'updateLicense');
            $r->addRoute('DELETE', '/license/{id:\d+}', 'deleteLicense');

            // role routes
            $r->addRoute('POST', '/role', 'addRole');
            $r->addRoute('GET', '/role', 'getRoles');
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
            case "/user-settings":
                return new UserSettingsController($entityManager);
            default:
                HttpHelper::sendStatusResponse(500, 'No Implementation Found');
                exit(1);
        }

    }

    public function trigRequest(array $routeInfo, string $route, EntityManager $entityManager): void
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                HttpHelper::sendStatusResponse(404, 'Route Not Found');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                HttpHelper::sendStatusResponse(405, 'Method Not Allowed');
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $controller = $this->setController($route, $entityManager);
                call_user_func_array([$controller, $handler], $vars);
                break;
            default:
                HttpHelper::sendStatusResponse(400, 'Unexpected Error');
        }
    }
}