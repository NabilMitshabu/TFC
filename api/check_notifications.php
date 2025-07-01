<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user']['id'];

// Récupérer le nombre de notifications non lues
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Récupérer la dernière notification
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY date_creation DESC LIMIT 1");
$stmt->execute([$user_id]);
$latest = $stmt->fetch();

echo json_encode([
    'unread' => (int)$unread,
    'latest' => $latest ? [
        'titre' => $latest['titre'],
        'message' => $latest['message']
    ] : null
]);