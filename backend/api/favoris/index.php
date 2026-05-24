<?php
// ============================================================
//  Artify — /api/favoris/index.php
//  GET    → liste des favoris
//  POST   → ajouter (body: { oeuvre_id }) ou retirer ({ action:"retirer", oeuvre_id })
//  DELETE → retirer (?oeuvre_id=xxx)
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

$methode = $_SERVER['REQUEST_METHOD'];
$session = verifierSession();
$userId  = $session['id'];
$colUser = getCollection('utilisateurs');

/**
 * Retire une œuvre des favoris.
 */
function retirerFavori(MongoDB\Collection $colUser, string $userId, string $oeuvreId): void
{
    if (empty($oeuvreId)) {
        repondreErreur('oeuvre_id est requis.');
    }
    try {
        $oidOeuvre = new MongoDB\BSON\ObjectId($oeuvreId);
    } catch (Exception $e) {
        repondreErreur('oeuvre_id invalide.');
    }
    $colUser->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($userId)],
        ['$pull' => ['favoris' => $oidOeuvre]]
    );
    repondreOK(['message' => 'Oeuvre retirée des favoris.']);
}

// ============================================================
// GET — liste des favoris
// ============================================================
if ($methode === 'GET') {

    $user = $colUser->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
    if ($user === null) {
        repondreErreur('Utilisateur introuvable.', 404);
    }

    $favorisIds = $user['favoris'] ?? [];

    if (empty($favorisIds)) {
        repondreOK(['favoris' => [], 'total' => 0]);
    }

    $colOeuvre  = getCollection('oeuvres');
    $oeuvresIds = array_map(
        fn($id) => new MongoDB\BSON\ObjectId((string)$id),
        $favorisIds
    );

    $curseur = $colOeuvre->find(['_id' => ['$in' => $oeuvresIds]]);
    $oeuvres = collectionVersTableau($curseur);

    repondreOK(['favoris' => $oeuvres, 'total' => count($oeuvres)]);
}

// ============================================================
// POST — ajouter ou retirer (action=retirer)
// ============================================================
elseif ($methode === 'POST') {
    $body = lireBodyJSON();

    if (($body['action'] ?? '') === 'retirer') {
        retirerFavori($colUser, $userId, sanitiser($body['oeuvre_id'] ?? ''));
    }

    $oeuvreId = sanitiser($body['oeuvre_id'] ?? '');

    if (empty($oeuvreId)) {
        repondreErreur('oeuvre_id est requis.');
    }

    try {
        $oidOeuvre = new MongoDB\BSON\ObjectId($oeuvreId);
    } catch (Exception $e) {
        repondreErreur('oeuvre_id invalide.');
    }

    $oeuvre = getCollection('oeuvres')->findOne(['_id' => $oidOeuvre]);
    if ($oeuvre === null) {
        repondreErreur('Oeuvre introuvable.', 404);
    }

    $colUser->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($userId)],
        ['$addToSet' => ['favoris' => $oidOeuvre]]
    );

    repondreOK(['message' => 'Oeuvre ajoutée aux favoris.'], 201);
}

// ============================================================
// DELETE — retirer un favori
// ============================================================
elseif ($methode === 'DELETE') {
    retirerFavori($colUser, $userId, sanitiser($_GET['oeuvre_id'] ?? ''));
}

else {
    repondreErreur('Méthode non autorisée.', 405);
}
