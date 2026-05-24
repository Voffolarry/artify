<?php
// ============================================================
//  Artify — /api/commandes/index.php
//  POST        → créer une commande (achat)
//  GET         → historique des commandes de l'utilisateur
//  PUT ?id=xxx → mettre à jour le statut de paiement
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

$methode   = $_SERVER['REQUEST_METHOD'];
$session   = verifierSession();   // toutes les routes nécessitent d'être connecté
$userId    = $session['id'];
$colCmd    = getCollection('commandes');
$colOeuvre = getCollection('oeuvres');

// ============================================================
// POST — créer une commande
// ============================================================
if ($methode === 'POST') {
    $body      = lireBodyJSON();
    $oeuvreId  = sanitiser($body['oeuvre_id'] ?? '');

    if (empty($oeuvreId)) {
        repondreErreur('oeuvre_id est requis.');
    }

    // Vérifier l'oeuvre
    try {
        $oid = new MongoDB\BSON\ObjectId($oeuvreId);
    } catch (Exception $e) {
        repondreErreur('oeuvre_id invalide.');
    }

    $oeuvre = $colOeuvre->findOne(['_id' => $oid]);
    if ($oeuvre === null) {
        repondreErreur('Oeuvre introuvable.', 404);
    }
    if ($oeuvre['statut'] !== 'disponible') {
        repondreErreur('Cette oeuvre n\'est plus disponible.', 409);
    }

    // Créer la commande
    $insertResult = $colCmd->insertOne([
        'utilisateur_id'  => new MongoDB\BSON\ObjectId($userId),
        'oeuvre_id'       => $oid,
        'montant'         => $oeuvre['prix'],
        'statut_paiement' => 'en_attente',
        'date_commande'   => new MongoDB\BSON\UTCDateTime(),
    ]);

    // Marquer l'oeuvre comme vendue
    $colOeuvre->updateOne(
        ['_id' => $oid],
        ['$set' => ['statut' => 'vendu']]
    );

    repondreOK([
        'message'     => 'Commande créée avec succès.',
        'commande_id' => (string) $insertResult->getInsertedId(),
        'montant'     => $oeuvre['prix'],
        'oeuvre'      => $oeuvre['titre'],
    ], 201);
}

// ============================================================
// GET — historique de l'utilisateur connecté
// ============================================================
elseif ($methode === 'GET') {
    $filtre = ['utilisateur_id' => new MongoDB\BSON\ObjectId($userId)];

    $curseur   = $colCmd->find($filtre, ['sort' => ['date_commande' => -1]]);
    $commandes = collectionVersTableau($curseur);

    // Enrichir avec les détails de l'oeuvre
    foreach ($commandes as &$c) {
        $oidOeuvre = $c['oeuvre_id'] ?? null;
        if ($oidOeuvre) {
            try {
                $o = $colOeuvre->findOne(
                    ['_id' => new MongoDB\BSON\ObjectId($oidOeuvre)],
                    ['projection' => ['titre' => 1, 'image_url' => 1, 'medium' => 1]]
                );
                $c['oeuvre_detail'] = $o ? objectIdVersString($o) : null;
            } catch (Exception $e) {
                $c['oeuvre_detail'] = null;
            }
        }
    }
    unset($c);

    repondreOK(['commandes' => $commandes, 'total' => count($commandes)]);
}

// ============================================================
// PUT — mise à jour statut paiement
// ============================================================
elseif ($methode === 'PUT') {
    if (empty($_GET['id'])) {
        repondreErreur('Paramètre id requis.');
    }

    try {
        $oid = new MongoDB\BSON\ObjectId(sanitiser($_GET['id']));
    } catch (Exception $e) {
        repondreErreur('Identifiant invalide.');
    }

    $body   = lireBodyJSON();
    $statut = sanitiser($body['statut_paiement'] ?? '');

    $statutsValides = ['en_attente', 'paye', 'rembourse', 'annule'];
    if (!in_array($statut, $statutsValides)) {
        repondreErreur('Statut invalide. Valeurs : ' . implode(', ', $statutsValides));
    }

    // Vérifier que la commande appartient à cet utilisateur
    $commande = $colCmd->findOne([
        '_id'            => $oid,
        'utilisateur_id' => new MongoDB\BSON\ObjectId($userId),
    ]);
    if ($commande === null) {
        repondreErreur('Commande introuvable.', 404);
    }

    $colCmd->updateOne(
        ['_id' => $oid],
        ['$set' => ['statut_paiement' => $statut]]
    );

    repondreOK(['message' => 'Statut de paiement mis à jour.', 'statut_paiement' => $statut]);
}

else {
    repondreErreur('Méthode non autorisée.', 405);
}
