<?php
// ============================================================
//  Artify — POST /api/auth/deconnexion.php
//  Destruction de la session
// ============================================================

require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondreErreur('Méthode non autorisée.', 405);
}

// Destruction complète de la session
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

repondreOK(['message' => 'Déconnexion réussie.']);
