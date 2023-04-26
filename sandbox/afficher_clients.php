<?php

require_once "bootstrap.php";

$id = $argv[1];
$client = $entityManager->find('Entity\Client', $id);

if ($client === null) {
    echo "Pas de client trouvé.\n";
    exit(1);
}

echo sprintf("-%s\n", $client->getNom());
