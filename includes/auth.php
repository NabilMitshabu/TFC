<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user']) && ($_SESSION['user']['logged_in'] ?? false) === true;
}

function getUserRole() {
    return $_SESSION['user']['role'] ?? null;
}

function isClient() {
    return isLoggedIn() && getUserRole() === 'client';
}

function isPrestataire() {
    return isLoggedIn() && getUserRole() === 'prestataire';
}

function requireAuth($redirect = '../views/signInClient.php') {
    if (!isLoggedIn()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header("Location: $redirect");
        exit();
    }
}

function requireClient($redirect = 'unauthorized.php') {
    requireAuth();
    if (!isClient()) {
        header("Location: $redirect");
        exit();
    }
}

function requirePrestataire($redirect = 'unauthorized.php') {
    requireAuth();
    if (!isPrestataire()) {
        header("Location: $redirect");
        exit();
    }
}

function getUserId() {
    return $_SESSION['user']['id'] ?? null;
}