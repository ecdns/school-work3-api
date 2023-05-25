<?php

declare(strict_types=1);

namespace Service;

use Controller\CompanyController;
use Controller\CompanySettingsController;
use Controller\CustomerController;
use Controller\CustomerStatusController;
use Controller\EstimateController;
use Controller\EstimateStatusController;
use Controller\InvoiceController;
use Controller\LicenseController;
use Controller\OrderFormController;
use Controller\ProductController;
use Controller\ProductFamilyController;
use Controller\ProjectController;
use Controller\ProjectStatusController;
use Controller\QuantityUnitController;
use Controller\RoleController;
use Controller\SupplierController;
use Controller\TaskController;
use Controller\TaskStatusController;
use Controller\UserController;
use Controller\UserSettingsController;
use Controller\VatController;
use DI\Container;
use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{

    private Dispatcher $dispatcher;
    private Request $request;
    private Container $container;

    public function __construct(Request $request, Container $container)
    {
        $this->request = $request;
        $this->container = $container;
        $this->dispatcher = $this->addRoutes();
    }

    private function addRoutes(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $r) {
            $r->addGroup('/api/v1', function (RouteCollector $r) {
                // user routes
                $r->addRoute('POST', '/user/login', [UserController::class, 'loginUser']);
                $r->addRoute('POST', '/user', [UserController::class, 'addUser']);
                $r->addRoute('GET', '/user/all', [UserController::class, 'getUsers']);
                $r->addRoute('GET', '/user/{id:\d+}', [UserController::class, 'getUserById']);
                $r->addRoute('PUT', '/user/{id:\d+}', [UserController::class, 'updateUser']);
                $r->addRoute('DELETE', '/user/{id:\d+}', [UserController::class, 'deleteUser']);

                // user settings routes
                $r->addRoute('POST', '/userSettings', [UserSettingsController::class, 'addUserSettings']);
                $r->addRoute('GET', '/userSettings/{id:\d+}', [UserSettingsController::class, 'getUserSettingsById']);
                $r->addRoute('PUT', '/userSettings/{id:\d+}', [UserSettingsController::class, 'updateUserSettings']);
                $r->addRoute('DELETE', '/userSettings/{id:\d+}', [UserSettingsController::class, 'deleteUserSettings']);

                // company routes
                $r->addRoute('POST', '/company', [CompanyController::class, 'addCompany']);
                $r->addRoute('GET', '/company/all', [CompanyController::class, 'getCompanies']);
                $r->addRoute('GET', '/company/{id:\d+}', [CompanyController::class, 'getCompanyById']);
                $r->addRoute('GET', '/company/{name}', [CompanyController::class, 'getCompanyByName']);
                $r->addRoute('PUT', '/company/{id:\d+}', [CompanyController::class, 'updateCompany']);
                $r->addRoute('DELETE', '/company/{id:\d+}', [CompanyController::class, 'deleteCompany']);

                // company settings routes
                $r->addRoute('POST', '/companySettings', [CompanySettingsController::class, 'addCompanySettings']);
                $r->addRoute('GET', '/companySettings/{id:\d+}', [CompanySettingsController::class, 'getCompanySettingsById']);
                $r->addRoute('PUT', '/companySettings/{id:\d+}', [CompanySettingsController::class, 'updateCompanySettings']);
                $r->addRoute('DELETE', '/companySettings/{id:\d+}', [CompanySettingsController::class, 'deleteCompanySettings']);

                // license routes
                $r->addRoute('POST', '/license', [LicenseController::class, 'addLicense']);
                $r->addRoute('GET', '/license/all', [LicenseController::class, 'getLicenses']);
                $r->addRoute('GET', '/license/{id:\d+}', [LicenseController::class, 'getLicenseById']);
                $r->addRoute('PUT', '/license/{id:\d+}', [LicenseController::class, 'updateLicense']);
                $r->addRoute('DELETE', '/license/{id:\d+}', [LicenseController::class, 'deleteLicense']);

                // role routes
                $r->addRoute('POST', '/role', [RoleController::class, 'addRole']);
                $r->addRoute('GET', '/role/all', [RoleController::class, 'getRoles']);
                $r->addRoute('GET', '/role/{id:\d+}', [RoleController::class, 'getRoleById']);
                $r->addRoute('PUT', '/role/{id:\d+}', [RoleController::class, 'updateRole']);
                $r->addRoute('DELETE', '/role/{id:\d+}', [RoleController::class, 'deleteRole']);

                // vat routes
                $r->addRoute('POST', '/vat', [VatController::class, 'addVat']);
                $r->addRoute('GET', '/vat/all', [VatController::class, 'getVats']);
                $r->addRoute('GET', '/vat/{id:\d+}', [VatController::class, 'getVatById']);
                $r->addRoute('PUT', '/vat/{id:\d+}', [VatController::class, 'updateVat']);
                $r->addRoute('DELETE', '/vat/{id:\d+}', [VatController::class, 'deleteVat']);

                // quantityUnit routes
                $r->addRoute('POST', '/quantityUnit', [QuantityUnitController::class, 'addQuantityUnit']);
                $r->addRoute('GET', '/quantityUnit/all', [QuantityUnitController::class, 'getQuantityUnits']);
                $r->addRoute('GET', '/quantityUnit/{id:\d+}', [QuantityUnitController::class, 'getQuantityUnitById']);
                $r->addRoute('PUT', '/quantityUnit/{id:\d+}', [QuantityUnitController::class, 'updateQuantityUnit']);
                $r->addRoute('DELETE', '/quantityUnit/{id:\d+}', [QuantityUnitController::class, 'deleteQuantityUnit']);

                // ProductFamily routes
                $r->addRoute('POST', '/productFamily', [ProductFamilyController::class, 'addProductFamily']);
                $r->addRoute('GET', '/productFamily/all', [ProductFamilyController::class, 'getProductFamilies']);
                $r->addRoute('GET', '/productFamily/{id:\d+}', [ProductFamilyController::class, 'getProductFamilyById']);
                $r->addRoute('PUT', '/productFamily/{id:\d+}', [ProductFamilyController::class, 'updateProductFamily']);
                $r->addRoute('DELETE', '/productFamily/{id:\d+}', [ProductFamilyController::class, 'deleteProductFamily']);

                // Supplier routes
                $r->addRoute('POST', '/supplier', [SupplierController::class, 'addSupplier']);
                $r->addRoute('GET', '/supplier/all', [SupplierController::class, 'getSuppliers']);
                $r->addRoute('GET', '/supplier/{id:\d+}', [SupplierController::class, 'getSupplierById']);
                $r->addRoute('PUT', '/supplier/{id:\d+}', [SupplierController::class, 'updateSupplier']);
                $r->addRoute('DELETE', '/supplier/{id:\d+}', [SupplierController::class, 'deleteSupplier']);

                // Supplier routes
                $r->addRoute('POST', '/product', [ProductController::class, 'addProduct']);
                $r->addRoute('GET', '/product/all', [ProductController::class, 'getProducts']);
                $r->addRoute('GET', '/product/company/{id:\d+}', [ProductController::class, 'getProductsByCompanyId']);
                $r->addRoute('GET', '/product/{id:\d+}', [ProductController::class, 'getProductById']);
                $r->addRoute('PUT', '/product/{id:\d+}', [ProductController::class, 'updateProduct']);
                $r->addRoute('DELETE', '/product/{id:\d+}', [ProductController::class, 'deleteProduct']);

                // Project routes
                $r->addRoute('POST', '/project', [ProjectController::class, 'addProject']);
                $r->addRoute('GET', '/project/all', [ProjectController::class, 'getProjects']);
                $r->addRoute('GET', '/project/{id:\d+}', [ProjectController::class, 'getProjectById']);
                $r->addRoute('GET', '/project/company/{id:\d+}', [ProjectController::class, 'getProjectsByCompany']);
                $r->addRoute('PUT', '/project/{id:\d+}', [ProjectController::class, 'updateProject']);
                $r->addRoute('DELETE', '/project/{id:\d+}', [ProjectController::class, 'deleteProject']);

                // ProjectStatus routes
                $r->addRoute('POST', '/projectStatus', [ProjectStatusController::class, 'addProjectStatus']);
                $r->addRoute('GET', '/projectStatus/all', [ProjectStatusController::class, 'getProjectStatuses']);
                $r->addRoute('GET', '/projectStatus/{id:\d+}', [ProjectStatusController::class, 'getProjectStatusById']);
                $r->addRoute('PUT', '/projectStatus/{id:\d+}', [ProjectStatusController::class, 'updateProjectStatus']);
                $r->addRoute('DELETE', '/projectStatus/{id:\d+}', [ProjectStatusController::class, 'deleteProjectStatus']);

                // Customer routes
                $r->addRoute('POST', '/customer', [CustomerController::class, 'addCustomer']);
                $r->addRoute('GET', '/customer/all', [CustomerController::class, 'getCustomers']);
                $r->addRoute('GET', '/customer/{id:\d+}', [CustomerController::class, 'getCustomerById']);
                $r->addRoute('GET', '/customer/company/{id:\d+}', [CustomerController::class, 'getCustomerByCompany']);
                $r->addRoute('PUT', '/customer/{id:\d+}', [CustomerController::class, 'updateCustomer']);
                $r->addRoute('DELETE', '/customer/{id:\d+}', [CustomerController::class, 'deleteCustomer']);

                //CustomerStatus routes
                $r->addRoute('POST', '/customerStatus', [CustomerStatusController::class, 'addCustomerStatus']);
                $r->addRoute('GET', '/customerStatus/all', [CustomerStatusController::class, 'getCustomerStatuses']);
                $r->addRoute('GET', '/customerStatus/{id:\d+}', [CustomerStatusController::class, 'getCustomerStatusById']);
                $r->addRoute('PUT', '/customerStatus/{id:\d+}', [CustomerStatusController::class, 'updateCustomerStatus']);
                $r->addRoute('DELETE', '/customerStatus/{id:\d+}', [CustomerStatusController::class, 'deleteCustomerStatus']);

                //Task routes
                $r->addRoute('POST', '/task', [TaskController::class, 'addTask']);
                $r->addRoute('GET', '/task/all', [TaskController::class, 'getTasks']);
                $r->addRoute('GET', '/task/{id:\d+}', [TaskController::class, 'getTaskById']);
                $r->addRoute('GET', '/task/project/{id:\d+}', [TaskController::class, 'getTasksByProject']);
                $r->addRoute('GET', '/task/user/{id:\d+}', [TaskController::class, 'getTasksByUser']);
                $r->addRoute('PUT', '/task/{id:\d+}', [TaskController::class, 'updateTask']);
                $r->addRoute('DELETE', '/task/{id:\d+}', [TaskController::class, 'deleteTask']);

                //TaskStatus routes
                $r->addRoute('POST', '/taskStatus', [TaskStatusController::class, 'addTaskStatus']);
                $r->addRoute('GET', '/taskStatus/all', [TaskStatusController::class, 'getTaskStatuses']);
                $r->addRoute('GET', '/taskStatus/{id:\d+}', [TaskStatusController::class, 'getTaskStatusById']);
                $r->addRoute('PUT', '/taskStatus/{id:\d+}', [TaskStatusController::class, 'updateTaskStatus']);
                $r->addRoute('DELETE', '/taskStatus/{id:\d+}', [TaskStatusController::class, 'deleteTaskStatus']);

                //Estimate routes
                $r->addRoute('POST', '/estimate', [EstimateController::class, 'addEstimate']);
                $r->addRoute('POST', '/estimate/{estimateId:\d+}/add/{productId:\d+}', [EstimateController::class, 'addProductsToEstimate']);
                $r->addRoute('POST', '/estimate/{estimateId:\d+}/remove/{productId:\d+}', [EstimateController::class, 'removeProductsFromEstimate']);
                $r->addRoute('GET', '/estimate/all', [EstimateController::class, 'getEstimates']);
                $r->addRoute('GET', '/estimate/{id:\d+}', [EstimateController::class, 'getEstimateById']);
                $r->addRoute('GET', '/estimate/project/{id:\d+}', [EstimateController::class, 'getEstimatesByProject']);
                $r->addRoute('PUT', '/estimate/{id:\d+}', [EstimateController::class, 'updateEstimate']);
                $r->addRoute('DELETE', '/estimate/{id:\d+}', [EstimateController::class, 'deleteEstimate']);

                //EstimateStatus routes
                $r->addRoute('POST', '/estimateStatus', [EstimateStatusController::class, 'addEstimateStatus']);
                $r->addRoute('GET', '/estimateStatus/all', [EstimateStatusController::class, 'getEstimateStatuses']);
                $r->addRoute('GET', '/estimateStatus/{id:\d+}', [EstimateStatusController::class, 'getEstimateStatusById']);
                $r->addRoute('PUT', '/estimateStatus/{id:\d+}', [EstimateStatusController::class, 'updateEstimateStatus']);
                $r->addRoute('DELETE', '/estimateStatus/{id:\d+}', [EstimateStatusController::class, 'deleteEstimateStatus']);

                //Invoice routes
                $r->addRoute('POST', '/invoice', [InvoiceController::class, 'addInvoice']);
                $r->addRoute('POST', '/invoice/{invoiceId:\d+}/add/{productId:\d+}', [InvoiceController::class, 'addProductsToInvoice']);
                $r->addRoute('POST', '/invoice/{invoiceId:\d+}/remove/{productId:\d+}', [InvoiceController::class, 'removeProductsFromInvoice']);
                $r->addRoute('GET', '/invoice/all', [InvoiceController::class, 'getInvoices']);
                $r->addRoute('GET', '/invoice/{id:\d+}', [InvoiceController::class, 'getInvoiceById']);
                $r->addRoute('GET', '/invoice/project/{id:\d+}', [InvoiceController::class, 'getInvoicesByProject']);
                $r->addRoute('PUT', '/invoice/{id:\d+}', [InvoiceController::class, 'updateInvoice']);

                //OrderForm routes
                $r->addRoute('POST', '/orderForm', [OrderFormController::class, 'addOrderForm']);
                $r->addRoute('POST', '/orderForm/{orderFormId:\d+}/add/{productId:\d+}', [OrderFormController::class, 'addProductsToOrderForm']);
                $r->addRoute('POST', '/orderForm/{orderFormId:\d+}/remove/{productId:\d+}', [OrderFormController::class, 'removeProductsFromOrderForm']);
                $r->addRoute('GET', '/orderForm/all', [OrderFormController::class, 'getOrderForms']);
                $r->addRoute('GET', '/orderForm/{id:\d+}', [OrderFormController::class, 'getOrderFormById']);
                $r->addRoute('GET', '/orderForm/project/{id:\d+}', [OrderFormController::class, 'getOrderFormsByProject']);
                $r->addRoute('PUT', '/orderForm/{id:\d+}', [OrderFormController::class, 'updateOrderForm']);
                $r->addRoute('DELETE', '/orderForm/{id:\d+}', [OrderFormController::class, 'deleteOrderForm']);
            });

        });
    }

    public function fetchRequestInfo(string $requestMethod, string $route): array
    {
        return $this->dispatcher->dispatch($requestMethod, $route);
    }

    public function trigRequest(array $requestInfo): void
    {
        switch ($requestInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->request->handleErrorAndQuit(404, new Exception('Not found'));
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $requestInfo[1];
                $this->request->handleErrorAndQuit(405, new Exception('Method not allowed. Allowed methods: ' . implode(', ', $allowedMethods)));
            case Dispatcher::FOUND:
                $controller = $this->container->get($requestInfo[1][0]);
                $method = $requestInfo[1][1];
                $params = $requestInfo[2];
                call_user_func_array([$controller, $method], $params);
                break;
            default:
                $this->request->handleErrorAndQuit(500, new Exception('Internal server error'));
        }
    }
}