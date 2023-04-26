<?php

use Entity\Application;

require_once "../bootstrap.php";

$newApplicationName = $argv[1];

$application = new Application();
$application->setNom($newApplicationName);

$entityManager->persist($application);
$entityManager->flush();

echo "Application créée avec l'ID " . $application->getId() . "\n";



