<?php

require_once "bootstrap.php";

$id = $argv[1];
$newName = $argv[2];

$application = $entityManager->find('Entity\Application', $id);

if ($application === null) {
    echo "L'application $id n'existe pas.\n";
    exit(1);
}

$application->setNom($newName);

$entityManager->flush();

