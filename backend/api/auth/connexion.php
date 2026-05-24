<?php
// ============================================================
//  Artify — POST /api/auth/connexion.php
//  Connexion d'un utilisateur existant
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondreErreur('Méthode non autorisée.', 405);
}

$body  = lireBodyJSON();
$email = sanitiser($body['email'] ?? '');
$mdp   = $body['mot_de_passe'] ?? '';

// ---- Validation basique ----
if (!validerEmail($email) || empty($mdp)) {
    repondreErreur('Email et mot de passe sont requis.');
}

// ---- Recherche utilisateur ----
$col         = getCollection('utilisateurs');
$utilisateur = $col->findOne(['email' => $email]);

if ($utilisateur === null) {
    repondreErreur('Email ou mot de passe incorrect.', 401);
}

// ---- Vérification mot de passe ----
if (!password_verify($mdp, $utilisateur['mot_de_passe'])) {
    repondreErreur('Email ou mot de passe incorrect.', 401);
}

$id = (string) $utilisateur['_id'];

// ---- Session ----
session_regenerate_id(true);    // protection fixation de session
$_SESSION['utilisateur_id']    = $id;
$_SESSION['utilisateur_email'] = $utilisateur['email'];
$_SESSION['utilisateur_role']  = $utilisateur['role'] ?? 'collectionneur';

repondreOK([
    'message' => 'Connexion réussie. Bonne visite sur Artify !',
    'utilisateur' => [
        'id'     => $id,
        'prenom' => $utilisateur['prenom'],
        'nom'    => $utilisateur['nom'],
        'email'  => $utilisateur['email'],
        'role'   => $utilisateur['role'] ?? 'collectionneur',
    ],
]);
