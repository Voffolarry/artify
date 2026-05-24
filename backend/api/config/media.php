<?php
// ============================================================
//  Artify — Chemins publics des images (servies par Apache)
//  Fichiers physiques : frontend/images/oeuvres/ et .../artistes/
//  URL navigateur     : MEDIA_BASE_URL/oeuvres/nom-fichier.jpg
// ============================================================

require_once __DIR__ . '/env.php';

/** Préfixe URL (racine du site sous XAMPP). */
define('MEDIA_BASE_URL', env('MEDIA_BASE_URL', '/artify/images'));

/** Associer chaque titre d'œuvre au nom de fichier dans frontend/images/oeuvres/ */
const OEUVRE_IMAGE_FILES = [
    'Lumière d\'Août'      => 'lumiere-aout.jpg',
    'Jardin Secret'        => 'jardin-secret.jpg',
    'Crépuscule Violet'    => 'crepuscule-violet.jpg',
    'São Paulo 4h du Matin' => 'sao-paulo-4h.jpg',
    'Marché de Belém'      => 'marche-belem.jpg',
    'Vide Fertile'         => 'vide-fertile.jpg',
    'Équilibre'            => 'equilibre.jpg',
    'Zellige Bleu'         => 'zellige-bleu.jpg',
    'Médina en Rose'       => 'medina-rose.jpg',
];

/** Clé "Prénom Nom" → fichier dans frontend/images/artistes/ */
const ARTISTE_IMAGE_FILES = [
    'Marielina Mofor' => 'marielina-mofor.jpg',
    'Isabelle Moreau' => 'isabelle-moreau.jpg',
    'Yuki Nakamura'   => 'yuki-nakamura.jpg',
    'Amira El Fassi'  => 'amira-elfassi.jpg',
];

function urlImageOeuvre(string $fichier): string
{
    return MEDIA_BASE_URL . '/oeuvres/' . ltrim($fichier, '/');
}

function urlImageArtiste(string $fichier): string
{
    return MEDIA_BASE_URL . '/artistes/' . ltrim($fichier, '/');
}
