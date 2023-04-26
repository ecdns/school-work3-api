<?php

require_once "bootstrap.php";

$clientRepository = $entityManager->getRepository('Entity\Client');
$clients = $clientRepository->findAll();

foreach ($clients as $client) {
    echo sprintf("-%s\n", $client->getNom());
}
