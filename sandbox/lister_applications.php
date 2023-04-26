<?php

require_once "bootstrap.php";

$applicationRepository = $entityManager->getRepository('Entity\Application');
$applications = $applicationRepository->findAll();

foreach ($applications as $application) {
    echo sprintf("-%s\n", $application->getNom());
}

