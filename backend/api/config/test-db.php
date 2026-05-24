<?php

echo "Collections : (aucune — base vide ou non initialisée)\n";

require 'vendor/autoload.php';

$uri = "mongodb+srv://gillesalainwaffo_db_user:artify2026@artifydb.i27xaht.mongodb.net/?appName=Artifydb";

try {

    $client = new MongoDB\Client($uri);

    echo "Connexion MongoDB Atlas réussie !";

} catch (Exception $e) {

    echo "Erreur : " . $e->getMessage();
}