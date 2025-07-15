<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$userId = $_SESSION['user']['id'];
$recipientId = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0;

if (!$recipientId) {
    echo json_encode([]);
    exit;
}

try {
    // Récupérer les messages entre l'utilisateur connecté et le destinataire
    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_id, m.recipient_id, m.contenu, m.date_envoi,
               u.prenom AS sender_prenom,
               COALESCE(p.photo_profil, '') AS sender_photo
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        LEFT JOIN prestataires p ON p.user_id = u.id
        WHERE (m.sender_id = :user_id AND m.recipient_id = :recipient_id)
           OR (m.sender_id = :recipient_id AND m.recipient_id = :user_id)
        ORDER BY m.date_envoi ASC
    ");
    $stmt->execute([
        'user_id' => $userId,
        'recipient_id' => $recipientId
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marquer les messages comme lus (si l'utilisateur connecté est le destinataire)
    $stmt = $pdo->prepare("UPDATE messages SET lu = 1 WHERE recipient_id = :user_id AND sender_id = :recipient_id");
    $stmt->execute([
        'user_id' => $userId,
        'recipient_id' => $recipientId
    ]);

    echo json_encode($messages);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur', 'details' => $e->getMessage()]);
}
