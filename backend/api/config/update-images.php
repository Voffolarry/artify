<?php
// ============================================================
//  Artify — Met à jour image_url / photo_url dans MongoDB
//  Sans supprimer les données (contrairement à seed.php)
//
//  1. Placez vos fichiers dans frontend/images/oeuvres/ et .../artistes/
//  2. Vérifiez les noms dans api/config/media.php
//  3. Exécutez : php api/config/update-images.php
// ============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/media.php';

$db = getDB();
$racineImages = realpath(__DIR__ . '/../../../frontend/images');
if ($racineImages === false) {
    $racineImages = realpath(__DIR__ . '/../../images');
}

echo "Dossier images : " . ($racineImages ?: '(introuvable)') . "\n\n";

// ---- Œuvres ----
$oeuvres = $db->selectCollection('oeuvres');
foreach (OEUVRE_IMAGE_FILES as $titre => $fichier) {
    $cheminLocal = $racineImages ? $racineImages . DIRECTORY_SEPARATOR . 'oeuvres' . DIRECTORY_SEPARATOR . $fichier : null;
    $url = urlImageOeuvre($fichier);

    if ($cheminLocal && !is_file($cheminLocal)) {
        echo "[AVERTISSEMENT] Fichier manquant : oeuvres/$fichier\n";
    }

    $r = $oeuvres->updateOne(['titre' => $titre], ['$set' => ['image_url' => $url]]);
    if ($r->getMatchedCount() === 0) {
        echo "[ABSENT] Aucune oeuvre avec le titre : $titre\n";
    } else {
        echo "[OK] $titre → $url\n";
    }
}

echo "\n";

// ---- Artistes ----
$artistes = $db->selectCollection('artistes');
foreach (ARTISTE_IMAGE_FILES as $nomComplet => $fichier) {
    [$prenom, $nom] = explode(' ', $nomComplet, 2);
    $cheminLocal = $racineImages ? $racineImages . DIRECTORY_SEPARATOR . 'artistes' . DIRECTORY_SEPARATOR . $fichier : null;
    $url = urlImageArtiste($fichier);

    if ($cheminLocal && !is_file($cheminLocal)) {
        echo "[AVERTISSEMENT] Fichier manquant : artistes/$fichier\n";
    }

    $r = $artistes->updateOne(
        ['prenom' => $prenom, 'nom' => $nom],
        ['$set' => ['photo_url' => $url]]
    );
    if ($r->getMatchedCount() === 0) {
        echo "[ABSENT] Artiste : $nomComplet\n";
    } else {
        echo "[OK] $nomComplet → $url\n";
    }
}

echo "\nTerminé. Rechargez le catalogue (Ctrl+F5).\n";
