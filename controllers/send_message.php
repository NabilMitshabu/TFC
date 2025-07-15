<?php

session_start();
require '../includes/db_connect.php';

$sender_id = $_SESSION['user']['id'];
$recipient_id = $_POST['recipient_id'];
$message = trim($_POST['message']);

if ($sender_id && $recipient_id && !empty($message)) {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, contenu) VALUES (?, ?, ?)");
    $stmt->execute([$sender_id, $recipient_id, $message]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Paramètres manquants']);
}
