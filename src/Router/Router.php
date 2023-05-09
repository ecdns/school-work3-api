<?php

declare(strict_types=1);

namespace Router;

use Controller\CompanyController;
use Controller\CompanySettingsController;
use Controller\LicenseController;
use Controller\ProductController;
use Controller\ProductFamilyController;
use Controller\QuantityUnitController;
use Controller\RoleController;
use Controller\SupplierController;
use Controller\UserController;
use Controller\UserSettingsController;
use Controller\VatController;
use Service\Request;
use Doctrine\ORM\EntityManager;
use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

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

            // vat routes
            $r->addRoute('POST', '/vat', 'addVat');
            $r->addRoute('GET', '/vat', 'getVats');
            $r->addRoute('GET', '/vat/{id:\d+}', 'getVatById');
            $r->addRoute('PUT', '/vat/{id:\d+}', 'updateVat');
            $r->addRoute('DELETE', '/vat/{id:\d+}', 'deleteVat');

            // quantityUnit routes
            $r->addRoute('POST', '/quantityUnit', 'addQuantityUnit');
            $r->addRoute('GET', '/quantityUnit', 'getQuantityUnits');
            $r->addRoute('GET', '/quantityUnit/{id:\d+}', 'getQuantityUnitById');
            $r->addRoute('PUT', '/quantityUnit/{id:\d+}', 'updateQuantityUnit');
            $r->addRoute('DELETE', '/quantityUnit/{id:\d+}', 'deleteQuantityUnit');

            // ProductFamily routes
            $r->addRoute('POST', '/productFamily', 'addProductFamily');
            $r->addRoute('GET', '/productFamily', 'getProductFamilies');
            $r->addRoute('GET', '/productFamily/{id:\d+}', 'getProductFamilyById');
            $r->addRoute('PUT', '/productFamily/{id:\d+}', 'updateProductFamily');
            $r->addRoute('DELETE', '/productFamily/{id:\d+}', 'deleteProductFamily');

            // Supplier routes
            $r->addRoute('POST', '/supplier', 'addSupplier');
            $r->addRoute('GET', '/supplier', 'getSuppliers');
            $r->addRoute('GET', '/supplier/{id:\d+}', 'getSupplierById');
            $r->addRoute('PUT', '/supplier/{id:\d+}', 'updateSupplier');
            $r->addRoute('DELETE', '/supplier/{id:\d+}', 'deleteSupplier');

            // Supplier routes
            $r->addRoute('POST', '/product', 'addProduct');
            $r->addRoute('GET', '/product', 'getProducts');
            $r->addRoute('GET', '/product/company/{id:\d+}', 'getProductsByCompany');
            $r->addRoute('GET', '/product/{id:\d+}', 'getProductById');
            $r->addRoute('PUT', '/product/{id:\d+}', 'updateProduct');
            $r->addRoute('DELETE', '/product/{id:\d+}', 'deleteProduct');
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
            case "/vat":
                return new VatController($entityManager);
            case "/quantityUnit":
                return new QuantityUnitController($entityManager);
            case "/productFamily":
                return new ProductFamilyController($entityManager);
            case "/supplier":
                return new SupplierController($entityManager);
            case "/product":
                return new ProductController($entityManager);
            default:
                return Request::handleErrorAndQuit(404, new Exception('Not found'));
        }

    }

    public function trigRequest(array $routeInfo, string $route, EntityManager $entityManager): void
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                Request::handleErrorAndQuit(404, new Exception('Not found'));
            case Dispatcher::METHOD_NOT_ALLOWED:
                Request::handleErrorAndQuit(405, new Exception('Method not allowed'));
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $controller = $this->setController($route, $entityManager);
                call_user_func_array([$controller, $handler], $vars);
                break;
            default:
                Request::handleErrorAndQuit(500, new Exception('Internal server error'));
        }
    }
}