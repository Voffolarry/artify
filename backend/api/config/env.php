<?php
// ============================================================
//  Artify — Chargement des variables d'environnement (.env)
//  Fichier attendu : backend/.env (ou htdocs/artify/.env après deploy)
// ============================================================

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$racineProjet = dirname(__DIR__, 2);
$cheminEnv    = $racineProjet . DIRECTORY_SEPARATOR . '.env';

if (is_readable($cheminEnv)) {
    Dotenv::createImmutable($racineProjet)->safeLoad();
}

/**
 * Lit une variable d'environnement (obligatoire ou optionnelle).
 */
function env(string $cle, ?string $defaut = null): string
{
    $valeur = $_ENV[$cle] ?? $_SERVER[$cle] ?? getenv($cle);
    if ($valeur === false || $valeur === null || $valeur === '') {
        if ($defaut !== null) {
            return $defaut;
        }
        throw new RuntimeException(
            "Variable d'environnement manquante : {$cle}. Copiez .env.example vers .env dans backend/."
        );
    }
    return (string) $valeur;
}
