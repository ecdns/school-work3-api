<?php

use DI\ContainerBuilder;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$entitiesPath = [__DIR__ . '/../src/Entity'];

// Création du container
$builder = new ContainerBuilder();

// Ajout des définitions des dépendances
$builder->addDefinitions([

    // Service\Log
    'Service\Log' => DI\autowire(),

    // Service\Doctrine (qui crée la connexion à la DB et fournit l'EntityManager)
    'Service\Doctrine' => DI\autowire()
        ->constructor(
            getenv('DB_HOST'),
            getenv('DB_PORT'),
            getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            $entitiesPath,
        ),

    // EntityManager
    'Doctrine\ORM\EntityManager' => DI\factory(function ($container) {
        return $container->get('Service\Doctrine')->getEntityManager();
    }),

    // Service\Http
    'Service\Http' => DI\autowire(),

    // Service\DAO
    'Service\DAO' => DI\autowire()
        ->constructorParameter('entityManager', DI\get('Doctrine\ORM\EntityManager')),

    // Service\Request
    'Service\Request' => DI\autowire()
        ->constructorParameter('http', DI\get('Service\Http'))
        ->constructorParameter('log', DI\get('Service\Log')),

    // Service\Router
    'Service\Router' => DI\autowire()
        ->constructorParameter('request', DI\get('Service\Request')),

    // Service\Auth
    'Service\Auth' => DI\autowire(),

    // Controllers
    'Controller\CompanyController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\CompanySettingsController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\LicenseController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\ProductController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\ProductFamilyController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\QuantityUnitController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\RoleController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\SupplierController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\UserController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request'))
        ->constructorParameter('auth', DI\get('Service\Auth')),
    'Controller\UserSettingsController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\VatController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\ContractTypeController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\ProjectController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\ProjectStatusController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\CustomerController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\CustomerStatusController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\TaskController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),
    'Controller\TaskStatusController' => DI\autowire()
        ->constructorParameter('dao', DI\get('Service\DAO'))
        ->constructorParameter('request', DI\get('Service\Request')),


]);

try {
    return $builder->build();
} catch (Exception $e) {
    $request = new Service\Request(new Service\Http(), new Service\Log());
    $request->handleErrorAndQuit(500, $e);
}
