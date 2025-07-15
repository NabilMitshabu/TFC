<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode([]);
    exit;
}

$userId = $_SESSION['user']['id'];

// Vérifier si c’est un prestataire (s’il est dans la table `prestataires`)
$stmt = $pdo->prepare("SELECT id FROM prestataires WHERE user_id = ?");
$stmt->execute([$userId]);
$isPrestataire = $stmt->fetchColumn();

if ($isPrestataire) {
    // === Prestataire connecté ===
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id AS contact_id, u.nom AS contact_nom, u.prenom AS contact_prenom,
                        c.telephone, MAX(m.date_envoi) AS last_message_date,
                        (
                            SELECT contenu FROM messages 
                            WHERE (sender_id = u.id AND recipient_id = :me)
                               OR (sender_id = :me AND recipient_id = u.id)
                            ORDER BY date_envoi DESC LIMIT 1
                        ) AS last_message,
                        (
                            SELECT COUNT(*) FROM messages 
                            WHERE sender_id = u.id AND recipient_id = :me AND lu = 0
                        ) AS unread_count
        FROM demandes_services ds
        JOIN services s ON ds.service_id = s.id
        JOIN users u ON ds.user_id = u.id
        LEFT JOIN client c ON u.id = c.user_id
        LEFT JOIN messages m ON (m.sender_id = u.id AND m.recipient_id = :me)
                             OR (m.sender_id = :me AND m.recipient_id = u.id)
        WHERE s.prestataire_id = :presta_id
        GROUP BY u.id
        ORDER BY last_message_date DESC
    ");
    $stmt->execute([
        'me' => $userId,
        'presta_id' => $isPrestataire
    ]);

} else {
    // === Client connecté ===
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id AS contact_id, u.nom AS contact_nom, u.prenom AS contact_prenom,
       ANY_VALUE(p.photo_profil) AS contact_photo,
       MAX(m.date_envoi) AS last_message_date,
                        (
                            SELECT contenu FROM messages 
                            WHERE (sender_id = u.id AND recipient_id = :me)
                               OR (sender_id = :me AND recipient_id = u.id)
                            ORDER BY date_envoi DESC LIMIT 1
                        ) AS last_message,
                        (
                            SELECT COUNT(*) FROM messages 
                            WHERE sender_id = u.id AND recipient_id = :me AND lu = 0
                        ) AS unread_count
        FROM demandes_services ds
        JOIN services s ON ds.service_id = s.id
        JOIN prestataires p ON s.prestataire_id = p.id
        JOIN users u ON p.user_id = u.id
        LEFT JOIN messages m ON (m.sender_id = u.id AND m.recipient_id = :me)
                             OR (m.sender_id = :me AND m.recipient_id = u.id)
        WHERE ds.user_id = :me AND ds.etat IN ('Validée', 'Terminée')
        GROUP BY u.id
        ORDER BY last_message_date DESC
    ");
    $stmt->execute(['me' => $userId]);
}

$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($conversations);
?>