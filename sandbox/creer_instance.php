<?php

use Entity\Instance;

require_once "bootstrap.php";

$clientId = $argv[1];
$applicationId = $argv[2];
$environnement = $argv[3];
$questionnaire = json_encode($argv[4]);

$client = $entityManager->find("Entity\Client", $clientId);
$application = $entityManager->find("Entity\Application", $applicationId);
if (!$client || !$application) {
    echo "Pas de client ou application trouvé pour les ids fournis.\n";
    exit(1);
}

$instance = new Instance();
$instance->definirClient($client);
$instance->definirApplication($application);
$instance->definirEnvironnement($environnement);
$instance->ajouterQuestionnaire($questionnaire);

$entityManager->persist($instance);
$entityManager->flush();

echo "Created Instance with ID " . $instance->getId() . " and environnement " . $instance->getEnvironnement() . "  and client " . $instance->getClient()->getNom() . " and application " . $instance->getApplication()->getNom() . " \n";