<?php

declare(strict_types=1);

namespace Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Doctrine\ORM\ORMSetup;
use Exception;

class Doctrine
{
    private string $dbHost;
    private string $dbPort;
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;
    private array $entitiesPath;
    private Request $request;


    public function __construct($dbHost, $dbPort , $dbName, $dbUser, $dbPassword, $entitiesPath, Request $request)
    {
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->entitiesPath = $entitiesPath;
        $this->request = $request;
    }

    private function getConfig() : Configuration
    {

        try {
            return ORMSetup::createAttributeMetadataConfiguration($this->entitiesPath, true);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, new Exception("Erreur de configuration de Doctrine : " . $e->getMessage()));
        }
    }

    private function getConnexion() : Connection
    {
        $dbParams = [
            'driver'   => 'pdo_mysql',
            'user'     => $this->dbUser,
            'host'     => $this->dbHost,
            'port'     => $this->dbPort,
            'password' => $this->dbPassword,
            'dbname'   => $this->dbName,
        ];

        try {
            return DriverManager::getConnection($dbParams, $this->getConfig());
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, new Exception("Erreur de connexion à la base de données : " . $e->getMessage()));
        }
    }

    public function getEntityManager(): EntityManager
    {
        try {
            return new EntityManager($this->getConnexion(), $this->getConfig());
        } catch (MissingMappingDriverImplementation|Exception $e) {
            $this->request->handleErrorAndQuit(500, new Exception("Erreur de configuration de Doctrine : " . $e->getMessage()));
        }
    }

}