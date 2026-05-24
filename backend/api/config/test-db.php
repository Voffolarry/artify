<?php

require_once __DIR__ . '/env.php';
echo "Collections : (aucune — base vide ou non initialisée)\n";

require 'vendor/autoload.php';

$uri = artifyEnv('MONGO_URI');

try {

    $client = new MongoDB\Client($uri);

    echo "Connexion MongoDB Atlas réussie !";

} catch (Exception $e) {

    echo "Erreur : " . $e->getMessage();
}