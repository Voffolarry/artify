<?php
// ============================================================
//  Artify — /api/profil/index.php
//  GET  → infos de l'utilisateur connecté + statistiques
//  PUT  → modifier nom/prénom/email
//  POST ?action=changer_mdp → changer le mot de passe
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

$methode = $_SERVER['REQUEST_METHOD'];
$session = verifierSession();
$userId  = $session['id'];
$colUser = getCollection('utilisateurs');
$oidUser = new MongoDB\BSON\ObjectId($userId);

// ============================================================
// GET — profil + statistiques
// ============================================================
if ($methode === 'GET') {

    $user = $colUser->findOne(
        ['_id' => $oidUser],
        ['projection' => ['mot_de_passe' => 0]]  // ne jamais retourner le hash
    );

    if ($user === null) {
        repondreErreur('Utilisateur introuvable.', 404);
    }

    $data = objectIdVersString($user);

    // Statistiques dynamiques
    $data['stats'] = [
        'achats'          => getCollection('commandes')->countDocuments(['utilisateur_id' => $oidUser]),
        'favoris'         => count($user['favoris'] ?? []),
        'artistes_suivis' => 0,  // fonctionnalité future
        'avis_rediges'    => getCollection('avis')->countDocuments(['utilisateur_id' => $oidUser]),
    ];

    repondreOK($data);
}

// ============================================================
// PUT — modifier nom/prénom/email
// ============================================================
elseif ($methode === 'PUT') {
    $body = lireBodyJSON();

    // Action spéciale : changement de mot de passe
    if (!empty($_GET['action']) && $_GET['action'] === 'changer_mdp') {

        $ancienMdp  = $body['ancien_mot_de_passe']  ?? '';
        $nouveauMdp = $body['nouveau_mot_de_passe'] ?? '';

        if (empty($ancienMdp) || empty($nouveauMdp)) {
            repondreErreur('Les deux mots de passe sont requis.');
        }
        if (strlen($nouveauMdp) < 8) {
            repondreErreur('Le nouveau mot de passe doit contenir au moins 8 caractères.');
        }

        $user = $colUser->findOne(['_id' => $oidUser]);
        if (!password_verify($ancienMdp, $user['mot_de_passe'])) {
            repondreErreur('Ancien mot de passe incorrect.', 403);
        }

        $colUser->updateOne(
            ['_id' => $oidUser],
            ['$set' => ['mot_de_passe' => password_hash($nouveauMdp, PASSWORD_BCRYPT)]]
        );

        repondreOK(['message' => 'Mot de passe modifié avec succès.']);
    }

    // Mise à jour infos générales
    $champs = [];

    if (!empty($body['prenom'])) $champs['prenom'] = sanitiser($body['prenom']);
    if (!empty($body['nom']))    $champs['nom']    = sanitiser($body['nom']);

    if (!empty($body['email'])) {
        $email = sanitiser($body['email']);
        if (!validerEmail($email)) {
            repondreErreur('Email invalide.');
        }
        // Vérifier unicité
        $existant = $colUser->findOne([
            'email' => $email,
            '_id'   => ['$ne' => $oidUser],
        ]);
        if ($existant !== null) {
            repondreErreur('Cet email est déjà utilisé.', 409);
        }
        $champs['email'] = $email;
        $_SESSION['utilisateur_email'] = $email;
    }

    if (empty($champs)) {
        repondreErreur('Aucun champ à mettre à jour.');
    }

    $colUser->updateOne(['_id' => $oidUser], ['$set' => $champs]);

    repondreOK(['message' => 'Profil mis à jour.', 'champs' => array_keys($champs)]);
}

else {
    repondreErreur('Méthode non autorisée.', 405);
}
