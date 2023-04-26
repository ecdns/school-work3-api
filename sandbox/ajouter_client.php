<?php

use Entity\Client;

require_once "../bootstrap.php";

$newClientName = $argv[1];

$client = new Client();
$client->setNom($newClientName);

$entityManager->persist($client);
$entityManager->flush();

echo "Client créé avec l'ID " . $client->getId() . "\n";


