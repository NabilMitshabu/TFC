<?php
// logout.php
require_once __DIR__ . '/../includes/db_connect.php';

// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Journalisation (optionnel)
error_log("Déconnexion de l'utilisateur ID: " . ($_SESSION['user']['id'] ?? 'inconnu'));

// Supprimer toutes les données de session
$_SESSION = [];

// Détruire le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Supprimer les cookies personnalisés
setcookie('remember_token', '', time() - 3600, '/');

// Invalider le token en base si vous utilisez "Se souvenir de moi"
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE token = ?");
    $stmt->execute([hash('sha256', $token)]);
}

// Headers anti-cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirection avec paramètre pour forcer le rafraîchissement
header("Location: ../index.php?logout=".time());
exit();