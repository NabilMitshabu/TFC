<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../controllers/functions.php';

// Démarrer la session avant tout en-tête
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    // Vérification CSRF
    $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($csrfHeader) || $csrfHeader !== ($_SESSION['csrf_token'] ?? '')) {
        throw new Exception('Token CSRF invalide', 403);
    }

    // Vérification authentification
    if (empty($_SESSION['user']['id'])) {
        throw new Exception('Authentification requise', 401);
    }

    $userId = (int)$_SESSION['user']['id'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $prestataireId = (int)($input['prestataire_id'] ?? 0);
            if ($prestataireId <= 0) {
                throw new Exception('ID prestataire invalide', 400);
            }

            // Vérifier si le prestataire existe
            $stmt = $pdo->prepare("SELECT id FROM prestataires WHERE id = ?");
            $stmt->execute([$prestataireId]);
            if (!$stmt->fetch()) {
                throw new Exception('Prestataire introuvable', 404);
            }

            $action = toggleFavorite($pdo, $userId, $prestataireId);
            echo json_encode([
                'status' => 'success',
                'action' => $action,
                'is_favorite' => $action === 'added',
                'total_favorites' => countUserFavorites($pdo, $userId)
            ]);
            break;

        default:
            throw new Exception('Méthode non autorisée', 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'debug' => [
            'user_id' => $_SESSION['user']['id'] ?? null,
            'csrf_received' => $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null,
            'csrf_expected' => $_SESSION['csrf_token'] ?? null
        ]
    ]);
}