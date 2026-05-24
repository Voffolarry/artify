<?php
// ============================================================
//  Artify — /api/artistes/index.php
//  GET         → liste tous les artistes
//  GET ?id=xxx → détail + oeuvres de l'artiste
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    repondreErreur('Méthode non autorisée.', 405);
}

$col = getCollection('artistes');

// ---- Détail par ID ----
if (!empty($_GET['id'])) {
    try {
        $oid = new MongoDB\BSON\ObjectId(sanitiser($_GET['id']));
    } catch (Exception $e) {
        repondreErreur('Identifiant invalide.');
    }

    $artiste = $col->findOne(['_id' => $oid]);
    if ($artiste === null) {
        repondreErreur('Artiste introuvable.', 404);
    }

    $data = objectIdVersString($artiste);

    // Récupérer les oeuvres associées
    $oeuvresCurseur = getCollection('oeuvres')->find(
        ['artiste_id' => $oid],
        ['sort' => ['date_ajout' => -1]]
    );
    $data['oeuvres'] = collectionVersTableau($oeuvresCurseur);
    $data['nb_oeuvres'] = count($data['oeuvres']);

    repondreOK($data);
}

// ---- Liste complète ----
$curseur  = $col->find([], ['sort' => ['nom' => 1]]);
$artistes = collectionVersTableau($curseur);

// Ajouter le nb d'oeuvres pour chaque artiste
$colOeuvres = getCollection('oeuvres');
foreach ($artistes as &$a) {
    try {
        $oid = new MongoDB\BSON\ObjectId($a['_id']);
        $a['nb_oeuvres'] = $colOeuvres->countDocuments(['artiste_id' => $oid]);
    } catch (Exception $e) {
        $a['nb_oeuvres'] = 0;
    }
}
unset($a);

repondreOK(['artistes' => $artistes, 'total' => count($artistes)]);
