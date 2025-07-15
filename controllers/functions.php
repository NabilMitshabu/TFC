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




if (!function_exists('getProfileImage')) {
    function getProfileImage($prenom, $nom, $photo = null) {
        if (!empty($photo)) {
            if (filter_var($photo, FILTER_VALIDATE_URL)) {
                return $photo;
            }

            $uploadPath = '/uploads/' . ltrim($photo, '/');
            if (file_exists(__DIR__ . '/../' . $uploadPath)) {
                return $uploadPath;
            }
        }

        $initials = '';
        if (!empty($prenom)) $initials .= substr($prenom, 0, 1);
        if (!empty($nom)) $initials .= substr($nom, 0, 1);

        return "https://ui-avatars.com/api/?name=" . urlencode($initials ?: 'U') . "&background=3b82f6&color=fff";
    }
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


function getUnreadNotificationsCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getRecentNotifications($pdo, $user_id, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY date_creation DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}
function getPendingEvaluations($pdo, $client_id) {
    $stmt = $pdo->prepare("
        SELECT e.*, u.nom AS presta_nom, s.nom AS service_nom
        FROM evaluations e
        JOIN prestataires p ON e.prestataire_id = p.id
        JOIN users u ON p.user_id = u.id
        JOIN services s ON e.service_id = s.id
        WHERE e.user_id = ? AND e.note IS NULL
        ORDER BY e.date_evaluation DESC
    ");
    $stmt->execute([$client_id]);
    return $stmt->fetchAll();
}

function getPastEvaluations($pdo, $client_id) {
    $stmt = $pdo->prepare("
        SELECT e.*, u.nom AS presta_nom, s.nom AS service_nom
        FROM evaluations e
        JOIN prestataires p ON e.prestataire_id = p.id
        JOIN users u ON p.user_id = u.id
        JOIN services s ON e.service_id = s.id
        WHERE e.user_id = ? AND e.note IS NOT NULL
        ORDER BY e.date_evaluation DESC
        LIMIT 5
    ");
    $stmt->execute([$client_id]);
    return $stmt->fetchAll();
}


function toggleFavorite(PDO $pdo, int $userId, int $prestataireId): string {
    // Vérification plus robuste de l'existence du prestataire
    $stmt = $pdo->prepare("SELECT id FROM prestataires WHERE id = ? AND etat_compte = 'Validé'");
    $stmt->execute([$prestataireId]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Prestataire invalide ou non validé");
    }

    // Vérification de l'existence du favori avec une requête plus efficace
    $stmt = $pdo->prepare("SELECT id FROM favoris WHERE user_id = ? AND prestataire_id = ? LIMIT 1");
    $stmt->execute([$userId, $prestataireId]);
    
    if ($stmt->fetch()) {
        // Suppression avec vérification des lignes affectées
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND prestataire_id = ?");
        $stmt->execute([$userId, $prestataireId]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Échec de la suppression du favori");
        }
        return 'removed';
    } else {
        // Insertion avec vérification de contrainte unique
        try {
            $stmt = $pdo->prepare("INSERT INTO favoris (user_id, prestataire_id, date_ajout) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $prestataireId]);
            return 'added';
        } catch (PDOException $e) {
            // Code d'erreur pour violation de contrainte unique
            if ($e->errorInfo[1] === 1062) {
                throw new Exception("Ce prestataire est déjà dans vos favoris");
            }
            throw $e;
        }
    }
}


function countUserFavorites($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}


function getUserFavorites(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT 
            f.prestataire_id,
            u.nom,
            u.prenom,
            p.photo_profil,
            u.prenom,
            p.photo_profil,
            p.type_prestataire,
            p.ville,
            p.commune,
            p.telephone,
            p.description,
            p.competence,
            f.date_ajout,
            ROUND((SELECT AVG(note) FROM evaluations WHERE prestataire_id = p.id), 1) as note_moyenne,
            (SELECT COUNT(*) FROM evaluations WHERE prestataire_id = p.id) as nombre_avis,
            (SELECT GROUP_CONCAT(DISTINCT s.nom SEPARATOR ', ') 
             FROM services s 
             WHERE s.prestataire_id = p.id) as services
        FROM 
            favoris f
        JOIN 
            prestataires p ON f.prestataire_id = p.id
        JOIN 
            users u ON p.user_id = u.id
        WHERE 
            f.user_id = ?
        GROUP BY
            f.prestataire_id, u.nom, u.prenom, p.photo_profil, p.type_prestataire,
            p.ville, p.commune, p.telephone, p.description, p.competence, f.date_ajout
        ORDER BY
            f.date_ajout DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Paramètres de session sécurisés
        session_set_cookie_params([
            'lifetime' => 86400, // 1 jour
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true, 
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_name('SECURE_SESSION');
        session_start();
        
        // Régénération périodique de l'ID de session
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

function countUnreadMessages($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND lu = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getRecentMessages($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT m.*, u.nom AS sender_nom, u.prenom AS sender_prenom
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.recipient_id = ?
        ORDER BY m.date_envoi DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
