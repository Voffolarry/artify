<?php
// ============================================================
//  Artify — Script de seed MongoDB
//  Exécuter UNE SEULE FOIS : php api/config/seed.php
// ============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/media.php';

$db = getDB();

// ---- Nettoyage ----
foreach (['utilisateurs','artistes','oeuvres','commandes','avis'] as $col) {
    $db->selectCollection($col)->drop();
    echo "Collection '$col' supprimée.\n";
}

// ---- ARTISTES ----
$artistes = $db->selectCollection('artistes');

$artistesData = [
    [
        'nom'        => 'Moreau',
        'prenom'     => 'Isabelle',
        'pays'       => 'France',
        'bio'        => 'Peintre impressionniste contemporaine, Isabelle Moreau explore la lumière et la couleur dans ses toiles lumineuses aux touches vibrantes.',
        'specialite' => 'Peinture',
        'photo_url'  => '',
        'date_creation' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'nom'        => 'Santos',
        'prenom'     => 'Carlos',
        'pays'       => 'Brésil',
        'bio'        => 'Photographe documentaire et artistique, Carlos Santos capture l\'âme des villes et la poésie du quotidien avec un regard unique.',
        'specialite' => 'Photographie',
        'photo_url'  => '',
        'date_creation' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'nom'        => 'Nakamura',
        'prenom'     => 'Yuki',
        'pays'       => 'Japon',
        'bio'        => 'Sculptrice minimaliste d\'origine japonaise, Yuki Nakamura travaille le bronze et la céramique pour créer des formes épurées chargées de sens.',
        'specialite' => 'Sculpture',
        'photo_url'  => '',
        'date_creation' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'nom'        => 'El Fassi',
        'prenom'     => 'Amira',
        'pays'       => 'Maroc',
        'bio'        => 'Aquarelliste marocaine reconnue, Amira El Fassi mêle géométrie islamique et aquarelle moderne pour des œuvres d\'une grande délicatesse.',
        'specialite' => 'Aquarelle',
        'photo_url'  => '',
        'date_creation' => new MongoDB\BSON\UTCDateTime(),
    ],
];

foreach ($artistesData as &$artiste) {
    $cle = $artiste['prenom'] . ' ' . $artiste['nom'];
    if (isset(ARTISTE_IMAGE_FILES[$cle])) {
        $artiste['photo_url'] = urlImageArtiste(ARTISTE_IMAGE_FILES[$cle]);
    }
}
unset($artiste);

$artistesInseres = $artistes->insertMany($artistesData);
$artisteIds = array_values($artistesInseres->getInsertedIds());
echo "Artistes insérés : " . count($artisteIds) . "\n";

// ---- OEUVRES ----
$oeuvres = $db->selectCollection('oeuvres');

$oeuvresData = [
    // Isabelle Moreau — Peinture
    [
        'artiste_id' => $artisteIds[0],
        'titre'      => 'Lumière d\'Août',
        'medium'     => 'Huile sur toile',
        'prix'       => 1800,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2024,
        'categories' => ['Peinture'],
        'description'=> 'Une explosion de lumière estivale capturée dans des teintes chaudes et vibrantes.',
        'dimensions' => '80 x 100 cm',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'artiste_id' => $artisteIds[0],
        'titre'      => 'Jardin Secret',
        'medium'     => 'Acrylique sur toile',
        'prix'       => 2400,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2025,
        'categories' => ['Peinture'],
        'description'=> 'Un refuge floral imaginaire entre ombre et clarté.',
        'dimensions' => '100 x 120 cm',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'artiste_id' => $artisteIds[0],
        'titre'      => 'Crépuscule Violet',
        'medium'     => 'Huile sur toile',
        'prix'       => 3200,
        'statut'     => 'vendu',
        'image_url'  => '',
        'annee'      => 2023,
        'categories' => ['Peinture'],
        'description'=> 'Le soleil se couche sur une mer de violets et d\'oranges.',
        'dimensions' => '120 x 90 cm',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],

    // Carlos Santos — Photographie
    [
        'artiste_id' => $artisteIds[1],
        'titre'      => 'São Paulo 4h du Matin',
        'medium'     => 'Photographie argentique',
        'prix'       => 950,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2024,
        'categories' => ['Photographie'],
        'description'=> 'La ville géante dans son silence nocturne, entre néons et béton.',
        'dimensions' => '60 x 90 cm — tirage limité 1/10',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'artiste_id' => $artisteIds[1],
        'titre'      => 'Marché de Belém',
        'medium'     => 'Photographie numérique',
        'prix'       => 750,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2025,
        'categories' => ['Photographie'],
        'description'=> 'Couleurs, odeurs et vie — un marché amazonien en plein élan.',
        'dimensions' => '50 x 75 cm — tirage limité 3/10',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],

    // Yuki Nakamura — Sculpture
    [
        'artiste_id' => $artisteIds[2],
        'titre'      => 'Vide Fertile',
        'medium'     => 'Bronze patiné',
        'prix'       => 5800,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2024,
        'categories' => ['Sculpture'],
        'description'=> 'Une forme ouverte qui interroge l\'espace entre plein et vide.',
        'dimensions' => '35 x 20 x 20 cm',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'artiste_id' => $artisteIds[2],
        'titre'      => 'Équilibre',
        'medium'     => 'Céramique émaillée',
        'prix'       => 1200,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2025,
        'categories' => ['Sculpture'],
        'description'=> 'Deux volumes en tension parfaite, suspendus dans l\'espace.',
        'dimensions' => '28 x 15 x 15 cm',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],

    // Amira El Fassi — Aquarelle
    [
        'artiste_id' => $artisteIds[3],
        'titre'      => 'Zellige Bleu',
        'medium'     => 'Aquarelle sur papier Arches',
        'prix'       => 680,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2025,
        'categories' => ['Aquarelle'],
        'description'=> 'Un hommage aux mosaïques de Fès, réinterprétées avec fluidité.',
        'dimensions' => '40 x 50 cm',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],
    [
        'artiste_id' => $artisteIds[3],
        'titre'      => 'Médina en Rose',
        'medium'     => 'Aquarelle sur papier Arches',
        'prix'       => 820,
        'statut'     => 'disponible',
        'image_url'  => '',
        'annee'      => 2024,
        'categories' => ['Aquarelle'],
        'description'=> 'Les ruelles de Marrakech baignées d\'une lumière rosée au crépuscule.',
        'dimensions' => '50 x 65 cm',
        'date_ajout' => new MongoDB\BSON\UTCDateTime(),
    ],
];

foreach ($oeuvresData as &$oeuvre) {
    if (isset(OEUVRE_IMAGE_FILES[$oeuvre['titre']])) {
        $oeuvre['image_url'] = urlImageOeuvre(OEUVRE_IMAGE_FILES[$oeuvre['titre']]);
    }
}
unset($oeuvre);

$oeuvresInseres = $oeuvres->insertMany($oeuvresData);
echo "Oeuvres insérées : " . count($oeuvresInseres->getInsertedIds()) . "\n";

// ---- UTILISATEUR de TEST ----
$utilisateurs = $db->selectCollection('utilisateurs');
$utilisateurs->insertOne([
    'nom'             => 'Dupont',
    'prenom'          => 'Marie',
    'email'           => 'marie@artify.fr',
    'mot_de_passe'    => password_hash('artify2026', PASSWORD_BCRYPT),
    'role'            => 'collectionneur',
    'favoris'         => [],
    'date_inscription'=> new MongoDB\BSON\UTCDateTime(),
]);
echo "Utilisateur de test inséré : marie@artify.fr / artify2026\n";

// ---- INDEX ----
$oeuvres->createIndex(['categories' => 1]);
$oeuvres->createIndex(['prix' => 1]);
$oeuvres->createIndex(['statut' => 1]);
$oeuvres->createIndex(['artiste_id' => 1]);
$utilisateurs->createIndex(['email' => 1], ['unique' => true]);
echo "Index créés.\n";

echo "\n✅ Seed Artify terminé avec succès !\n";
