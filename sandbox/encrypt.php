<?php


$value = 'test78';

// Calcul de la taille du bloc de chiffrement
$block_size = openssl_cipher_iv_length('aes-256-cbc');

// Calcul du padding à ajouter
$padding_size = $block_size - (strlen($value) % $block_size);
$padding = str_repeat(chr($padding_size), $padding_size);

// Ajout du padding à la chaîne de caractères
$value_padded = $value . $padding;

// Génération d'un IV aléatoire
//$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

// Encodage de la valeur avec l'IV spécifié
$encrypted = openssl_encrypt($value_padded, 'aes-256-cbc', 'clés loin Perrache axe !', OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);

// Affichage de la valeur encodée en hexadécimal
echo bin2hex($encrypted)."\n";


// Déchiffrement de la valeur avec l'IV spécifié
$decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', 'clés loin Perrache axe !', OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);

// Retrait du padding de la valeur déchiffrée
$padding_size = ord(substr($decrypted, -1));
$decrypted = substr($decrypted, 0, -$padding_size);

// Affichage de la valeur déchiffrée
echo $decrypted."\n";







