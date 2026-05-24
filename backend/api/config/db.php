<?php
// ============================================================
//  Artify — Connexion MongoDB
//  Variables : backend/.env (voir .env.example)
// ============================================================

require_once __DIR__ . '/env.php';

/**
 * Configuration chargée à la demande (évite erreur HTML si .env manquant avant helpers).
 */
function artifyEnv(string $cle, ?string $defaut = null): string
{
    static $charge = false;
    static $cache  = [];

    if (!$charge) {
        try {
            $cache['MONGO_URI']  = env('MONGO_URI');
            $cache['MONGO_DB']   = env('MONGO_DB', 'artify');
            $cache['SECRET_KEY'] = env('SECRET_KEY');
            $charge = true;
        } catch (Throwable $e) {
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(500);
                echo json_encode([
                    'succes' => false,
                    'erreur' => $e->getMessage(),
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            throw $e;
        }
    }

    if (!array_key_exists($cle, $cache)) {
        throw new RuntimeException("Configuration inconnue : {$cle}");
    }
    return $cache[$cle];
}

/** @deprecated Utiliser artifyEnv() — compatibilité */
function getSecretKey(): string
{
    return artifyEnv('SECRET_KEY');
}

/**
 * Retourne l'instance de la base de données MongoDB.
 */
function getDB(): MongoDB\Database
{
    static $db = null;
    if ($db === null) {
        try {
            $client = new MongoDB\Client(artifyEnv('MONGO_URI'));
            $db     = $client->selectDatabase(artifyEnv('MONGO_DB'));
        } catch (Throwable $e) {
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(500);
                echo json_encode([
                    'succes' => false,
                    'erreur' => 'Connexion MongoDB échouée : ' . $e->getMessage(),
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            throw $e;
        }
    }
    return $db;
}

/**
 * Retourne une collection MongoDB.
 */
function getCollection(string $name): MongoDB\Collection
{
    return getDB()->selectCollection($name);
}
