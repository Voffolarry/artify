<?php
// ============================================================
//  Artify — /api/oeuvres/index.php
//  GET    → liste (filtres, tri)
//  GET ?id=xxx → détail d'une oeuvre
//  PUT ?id=xxx → mise à jour statut (admin)
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

$methode = $_SERVER['REQUEST_METHOD'];
$col     = getCollection('oeuvres');

// ============================================================
// GET — liste ou détail
// ============================================================
if ($methode === 'GET') {

    // ---- Détail par ID ----
    if (!empty($_GET['id'])) {
        try {
            $oid  = new MongoDB\BSON\ObjectId(sanitiser($_GET['id']));
        } catch (Exception $e) {
            repondreErreur('Identifiant invalide.');
        }

        $oeuvre = $col->findOne(['_id' => $oid]);
        if ($oeuvre === null) {
            repondreErreur('Oeuvre introuvable.', 404);
        }

        // Ajouter les infos artiste
        $artiste = getCollection('artistes')->findOne(
            ['_id' => $oeuvre['artiste_id']],
            ['projection' => ['mot_de_passe' => 0]]
        );

        $data            = objectIdVersString($oeuvre);
        $data['artiste'] = $artiste ? objectIdVersString($artiste) : null;

        repondreOK($data);
    }

    // ---- Liste avec filtres ----
    $filtre = [];

    // Filtre catégorie
    if (!empty($_GET['categorie'])) {
        $cat = sanitiser($_GET['categorie']);
        $filtre['categories'] = $cat;   // MongoDB cherche dans le tableau
    }

    // Filtre statut
    if (!empty($_GET['statut'])) {
        $filtre['statut'] = sanitiser($_GET['statut']);
    }

    // Filtre artiste
    if (!empty($_GET['artiste_id'])) {
        try {
            $filtre['artiste_id'] = new MongoDB\BSON\ObjectId(sanitiser($_GET['artiste_id']));
        } catch (Exception $e) {
            repondreErreur('artiste_id invalide.');
        }
    }

    // Tri
    $tri = ['date_ajout' => -1];   // défaut : plus récent
    $triParam = sanitiser($_GET['tri'] ?? '');

    if ($triParam === 'prix_asc')  $tri = ['prix' => 1];
    if ($triParam === 'prix_desc') $tri = ['prix' => -1];
    if ($triParam === 'recent')    $tri = ['date_ajout' => -1];

    // Pagination
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $limite   = min(50, max(1, (int)($_GET['limite'] ?? 12)));
    $skip     = ($page - 1) * $limite;

    $total   = $col->countDocuments($filtre);
    $curseur = $col->find($filtre, [
        'sort'  => $tri,
        'skip'  => $skip,
        'limit' => $limite,
    ]);

    $oeuvres = collectionVersTableau($curseur);

    // Enrichir chaque oeuvre avec le nom de l'artiste
    $artisteCache = [];
    foreach ($oeuvres as &$o) {
        $aid = $o['artiste_id'] ?? null;
        if ($aid) {
            if (!isset($artisteCache[$aid])) {
                try {
                    $a = getCollection('artistes')->findOne(
                        ['_id' => new MongoDB\BSON\ObjectId($aid)],
                        ['projection' => ['nom' => 1, 'prenom' => 1, 'pays' => 1]]
                    );
                    $artisteCache[$aid] = $a ? objectIdVersString($a) : null;
                } catch (Exception $e) {
                    $artisteCache[$aid] = null;
                }
            }
            $o['artiste'] = $artisteCache[$aid];
        }
    }
    unset($o);

    repondreOK([
        'oeuvres'    => $oeuvres,
        'total'      => $total,
        'page'       => $page,
        'pages'      => (int) ceil($total / $limite),
        'limite'     => $limite,
    ]);
}

// ============================================================
// PUT — mise à jour statut (authentifié)
// ============================================================
elseif ($methode === 'PUT') {
    verifierSession();   // doit être connecté

    if (empty($_GET['id'])) {
        repondreErreur('Paramètre id requis.');
    }

    try {
        $oid = new MongoDB\BSON\ObjectId(sanitiser($_GET['id']));
    } catch (Exception $e) {
        repondreErreur('Identifiant invalide.');
    }

    $body   = lireBodyJSON();
    $statut = sanitiser($body['statut'] ?? '');

    if (!in_array($statut, ['disponible', 'vendu', 'reserve'])) {
        repondreErreur('Statut invalide. Valeurs acceptées : disponible, vendu, reserve.');
    }

    $result = $col->updateOne(
        ['_id' => $oid],
        ['$set' => ['statut' => $statut]]
    );

    if ($result->getMatchedCount() === 0) {
        repondreErreur('Oeuvre introuvable.', 404);
    }

    repondreOK(['message' => 'Statut mis à jour.', 'statut' => $statut]);
}

else {
    repondreErreur('Méthode non autorisée.', 405);
}
