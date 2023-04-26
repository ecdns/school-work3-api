<?php
require_once "bootstrap.php";

$id = $argv[1];
$newName = $argv[2];

$client = $entityManager->find('Entity\Client', $id);

if ($client === null) {
    echo "Le client $id n'existe pas.\n";
    exit(1);
}

$client->setNom($newName);

$entityManager->flush();
