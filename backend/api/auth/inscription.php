<?php
// ============================================================
//  Artify — POST /api/auth/inscription.php
//  Inscription d'un nouvel utilisateur
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondreErreur('Méthode non autorisée.', 405);
}

$body = lireBodyJSON();

// ---- Validation ----
$prenom = sanitiser($body['prenom'] ?? '');
$nom    = sanitiser($body['nom']    ?? '');
$email  = sanitiser($body['email']  ?? '');
$mdp    = $body['mot_de_passe'] ?? '';  // ne pas sanitiser avant hash

if (empty($prenom) || empty($nom)) {
    repondreErreur('Prénom et nom sont requis.');
}
if (!validerEmail($email)) {
    repondreErreur('Adresse email invalide.');
}
if (strlen($mdp) < 8) {
    repondreErreur('Le mot de passe doit contenir au moins 8 caractères.');
}

// ---- Unicité email ----
$col = getCollection('utilisateurs');

$existant = $col->findOne(['email' => $email]);
if ($existant !== null) {
    repondreErreur('Un compte existe déjà avec cet email.', 409);
}

// ---- Insertion ----
$insertResult = $col->insertOne([
    'nom'              => $nom,
    'prenom'           => $prenom,
    'email'            => $email,
    'mot_de_passe'     => password_hash($mdp, PASSWORD_BCRYPT),
    'role'             => 'collectionneur',
    'favoris'          => [],
    'date_inscription' => new MongoDB\BSON\UTCDateTime(),
]);

$id = (string) $insertResult->getInsertedId();

// ---- Démarrage session ----
$_SESSION['utilisateur_id']    = $id;
$_SESSION['utilisateur_email'] = $email;
$_SESSION['utilisateur_role']  = 'collectionneur';

repondreOK([
    'message' => 'Inscription réussie. Bienvenue sur Artify !',
    'utilisateur' => [
        'id'     => $id,
        'prenom' => $prenom,
        'nom'    => $nom,
        'email'  => $email,
        'role'   => 'collectionneur',
    ],
], 201);
