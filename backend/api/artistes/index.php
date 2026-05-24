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

// ---- Liste complète — artiste du mois en premier ----
$colOeuvres = getCollection('oeuvres');
$artistes   = [];

// 1. Artiste du mois en premier
$artisteDuMois = $col->findOne(['artiste_du_mois' => true]);
if ($artisteDuMois) {
    $a = objectIdVersString($artisteDuMois);
    try {
        $oid = new MongoDB\BSON\ObjectId($a['_id']);
        $a['nb_oeuvres'] = $colOeuvres->countDocuments(['artiste_id' => $oid]);
    } catch (Exception $e) {
        $a['nb_oeuvres'] = 0;
    }
    $artistes[] = $a;
}

// 2. Les autres triés alphabétiquement
$curseurReste = $col->find(
    ['artiste_du_mois' => ['$ne' => true]],
    ['sort' => ['nom' => 1]]
);
foreach (collectionVersTableau($curseurReste) as $a) {
    try {
        $oid = new MongoDB\BSON\ObjectId($a['_id']);
        $a['nb_oeuvres'] = $colOeuvres->countDocuments(['artiste_id' => $oid]);
    } catch (Exception $e) {
        $a['nb_oeuvres'] = 0;
    }
    $artistes[] = $a;
}

repondreOK(['artistes' => $artistes, 'total' => count($artistes)]);