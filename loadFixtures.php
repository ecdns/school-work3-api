<?php
// loadFixtures.php

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

require_once "bootstrap.php";

// Chargement des fixtures
$loader = new Loader();
$loader->loadFromDirectory(__DIR__.'/src/DataFixtures');

$purger = new ORMPurger();
$executor = new ORMExecutor($entityManager, $purger);
$executor->execute($loader->getFixtures());