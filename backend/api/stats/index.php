<?php
// ============================================================
//  Artify — GET /api/stats/index.php
//  Statistiques globales pour la page d'accueil
// ============================================================

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    repondreErreur('Méthode non autorisée.', 405);
}

$db = getDB();

// Nb oeuvres disponibles
$nbOeuvres = $db->selectCollection('oeuvres')->countDocuments(['statut' => 'disponible']);

// Nb artistes
$nbArtistes = $db->selectCollection('artistes')->countDocuments();

// Nb pays uniques
$pays = $db->selectCollection('artistes')->distinct('pays');
$nbPays = count($pays);

// Nb collectionneurs (utilisateurs avec au moins 1 commande)
$nbCollectionneurs = count(
    $db->selectCollection('commandes')->distinct('utilisateur_id')
);

repondreOK([
    'oeuvres'        => $nbOeuvres,
    'artistes'       => $nbArtistes,
    'pays'           => $nbPays,
    'collectionneurs'=> $nbCollectionneurs,
]);
