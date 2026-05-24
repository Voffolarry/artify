<?php
// ============================================================
//  Artify — Helpers globaux (CORS, réponses JSON, session)
// ============================================================

// ---------- CORS (sessions : origine explicite, pas *) ----------
header('Content-Type: application/json; charset=UTF-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$originesLocales = ['http://localhost', 'http://127.0.0.1'];
if (in_array($origin, $originesLocales, true)
    || preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------- Session (cookie valable pour tout /artify/) ----------
if (session_status() === PHP_SESSION_NONE) {
    $base = '/artify';
    if (!empty($_SERVER['SCRIPT_NAME']) && preg_match('#^(/[^/]+)/#', $_SERVER['SCRIPT_NAME'], $m)) {
        $base = $m[1];
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $base . '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------- Helpers réponses ----------

function repondreOK(mixed $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['succes' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function repondreErreur(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['succes' => false, 'erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- Lecture du body JSON ----------

function lireBodyJSON(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ---------- Vérification session ----------

function verifierSession(): array
{
    if (empty($_SESSION['utilisateur_id'])) {
        repondreErreur('Non authentifié. Veuillez vous connecter.', 401);
    }
    return [
        'id'    => $_SESSION['utilisateur_id'],
        'email' => $_SESSION['utilisateur_email'] ?? '',
        'role'  => $_SESSION['utilisateur_role']  ?? 'visiteur',
    ];
}

// ---------- Sanitisation ----------

function sanitiser(mixed $valeur): mixed
{
    if (is_string($valeur)) {
        return htmlspecialchars(strip_tags(trim($valeur)), ENT_QUOTES, 'UTF-8');
    }
    if (is_array($valeur)) {
        return array_map('sanitiser', $valeur);
    }
    return $valeur;
}

// ---------- Validation email ----------

function validerEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ---------- Conversion ObjectId MongoDB ----------

function objectIdVersString(mixed $doc): array
{
    if ($doc === null) return [];
    $arr = (array) $doc;
    if (isset($arr['_id']) && $arr['_id'] instanceof MongoDB\BSON\ObjectId) {
        $arr['_id'] = (string) $arr['_id'];
    }
    // Nettoyer les autres champs BSON
    foreach ($arr as $k => $v) {
        if ($v instanceof MongoDB\BSON\ObjectId) $arr[$k] = (string) $v;
        if ($v instanceof MongoDB\BSON\UTCDateTime) $arr[$k] = $v->toDateTime()->format('c');
    }
    return $arr;
}

function collectionVersTableau(iterable $curseur): array
{
    $result = [];
    foreach ($curseur as $doc) {
        $result[] = objectIdVersString($doc);
    }
    return $result;
}
