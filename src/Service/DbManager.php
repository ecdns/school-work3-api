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

class DbManager
{
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;
    private string $dbPort;
    private array $entityPath;
    private bool $isDevMode;
    private Configuration $config;

    public function __construct(string $dbHost, string $dbName, string $dbUser, string $dbPassword, string $dbPort, array $entityPath, bool $isDevMode)
    {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbPort = $dbPort;
        $this->entityPath = $entityPath;
        $this->isDevMode = $isDevMode;
        $this->config = ORMSetup::createAttributeMetadataConfiguration($this->entityPath, $this->isDevMode);
    }

    public function getConnexion() : Connection
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
            return DriverManager::getConnection($dbParams, $this->config);
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getEntityManager(Connection $connection): EntityManager
    {
        try {
            return new EntityManager($connection, $this->config);
        } catch (MissingMappingDriverImplementation $e) {
            throw new Exception($e->getMessage());
        }
    }
}