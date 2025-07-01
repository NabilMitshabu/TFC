<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../controllers/functions.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// Récupération des notifications
$notifications = getNotifications($pdo, $_SESSION['user']['id'], 50);

// Afficher uniquement le contenu des notifications
foreach ($notifications as $notification): ?>
    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
        <div class="flex items-start">
            <span class="material-icons text-blue-500 mr-3"><?= htmlspecialchars($notification['icon']) ?></span>
            <div>
                <h3 class="font-medium"><?= htmlspecialchars($notification['titre']) ?></h3>
                <p class="text-gray-600"><?= htmlspecialchars($notification['message']) ?></p>
                <p class="text-xs text-gray-400 mt-2">
                    <?= date('d/m/Y H:i', strtotime($notification['date_creation'])) ?>
                </p>
            </div>
        </div>
    </div>
<?php endforeach; ?>