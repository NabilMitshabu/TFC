<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rediriger si tentative d'accès après déconnexion
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time()-3600, '/');
}

if (basename($_SERVER['PHP_SELF']) != 'login.php' && !isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}