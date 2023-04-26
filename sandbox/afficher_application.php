<?php

require_once "bootstrap.php";

$id = $argv[1];
$application = $entityManager->find('Entity\Application', $id);

if ($application === null) {
    echo "Pas d'application trouvée.\n";
    exit(1);
}

echo sprintf("-%s\n", $application->getNom());

