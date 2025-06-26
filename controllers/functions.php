<?php
function getUserData(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT u.*, c.ville, c.commune, c.telephone 
        FROM users u
        JOIN Client c ON u.id = c.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getClientStats(PDO $pdo, int $userId): array {
      if ($userId === null) {
        return [
            'current_requests' => 0,
            'completed_services' => 0,
            'favorite_providers' => 0
        ];
    }
    // Demandes en cours
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_services WHERE user_id = ? AND etat = 'Validée'");
    $stmt->execute([$userId]);
    $currentRequests = $stmt->fetchColumn();

    // Services terminés
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM demandes_services 
        WHERE user_id = ? AND etat = 'Terminée'
    ");
    $stmt->execute([$userId]);
    $completedServices = $stmt->fetchColumn();

    // Prestataires favoris
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ?");
    $stmt->execute([$userId]);
    $favoriteProviders = $stmt->fetchColumn();

    return [
        'current_requests' => $currentRequests,
        'completed_services' => $completedServices,
        'favorite_providers' => $favoriteProviders
    ];
}

function getNotifications(PDO $pdo, int $userId, int $limit = 5): array {
    $limit = (int) $limit; // sécurise l'injection
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY date_creation DESC 
        LIMIT $limit
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getRecentMessages(PDO $pdo, int $userId, int $limit = 3): array {
    $limit = (int) $limit; // sécurisation
    $stmt = $pdo->prepare("
        SELECT m.*, u.nom as sender_name, p.photo_profil as sender_photo
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        LEFT JOIN prestataires p ON u.id = p.user_id
        WHERE m.recipient_id = ?
        ORDER BY m.date_envoi DESC
        LIMIT $limit
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getPendingEvaluations(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT d.id as demande_id, d.date_heure_rdv as date_service, 
               s.nom as service_nom, p.id as prestataire_id,
               CONCAT(u.prenom, ' ', u.nom) as prestataire_nom,
               p.photo_profil as prestataire_photo
        FROM demandes_services d
        JOIN services s ON d.service_id = s.id
        JOIN prestataires p ON s.prestataire_id = p.id
        JOIN users u ON p.user_id = u.id
        LEFT JOIN evaluations e ON e.demande_id = d.id
        WHERE d.user_id = ? 
        AND d.etat = 'Terminée'
        AND e.id IS NULL
        ORDER BY d.date_heure_rdv DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPastEvaluations(PDO $pdo, int $userId, int $limit = 2): array {
    $limit = (int) $limit; // sécurisation
    $stmt = $pdo->prepare("
        SELECT e.*, e.note, e.commentaire, e.date_evaluation,
               CONCAT(u.prenom, ' ', u.nom) as prestataire_nom,
               p.photo_profil as prestataire_photo,
               s.nom as service_nom
        FROM evaluations e
        JOIN prestataires p ON e.prestataire_id = p.id
        JOIN users u ON p.user_id = u.id
        JOIN services s ON e.service_id = s.id
        WHERE e.user_id = ?
        ORDER BY e.date_evaluation DESC
        LIMIT $limit
    ");
    $stmt->execute([$userId]); // ✅ le LIMIT n’est plus un paramètre
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getProfileImage(?string $photo, string $name): string {
    return $photo ?: 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&color=fff&size=128';
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $timeDiff = time() - $time;
    
    if ($timeDiff < 60) return 'À l\'instant';
    if ($timeDiff < 3600) return floor($timeDiff/60) . ' min';
    if ($timeDiff < 86400) return floor($timeDiff/3600) . ' h';
    if ($timeDiff < 604800) return floor($timeDiff/86400) . ' j';
    
    return date('d/m/Y', $time);
}

function truncate(string $text, int $length): string {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function countUnreadNotifications(PDO $pdo, int $userId): int {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erreur countUnreadNotifications: " . $e->getMessage());
        return 0;
    }
}

function countUnreadMessages(PDO $pdo, int $userId): int {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erreur countUnreadMessages: " . $e->getMessage());
        return 0;
    }
}