<?php 
// Vérifie si la session n'est pas déjà démarrée avant de l'initialiser
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupération des notifications si l'utilisateur est connecté
$unreadCount = 0;
$notifications = [];

if (!empty($_SESSION['user']['id'])) {
    require_once '../includes/db_connect.php';
    
    // Compte les notifications non lues
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user']['id']]);
    $unreadCount = $stmt->fetchColumn();
    
    // Récupère les 5 dernières notifications
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY date_creation DESC LIMIT 5");
    $stmt->execute([$_SESSION['user']['id']]);
    $notifications = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClicService</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .notification-badge {
            top: -0.5rem;
            right: -0.5rem;
            font-size: 0.75rem;
            height: 1.25rem;
            min-width: 1.25rem;
        }
        .notification-panel {
            max-height: 80vh;
            width: 24rem;
            right: 0;
        }
        .group:hover .group-hover\:block {
            display: block;
        }
    </style>
</head>
<body>
    <header class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold text-blue-600">ClicService</a>
            <nav class="flex items-center space-x-6 text-sm font-medium text-gray-600">
                <a href="../../index.php" class="hover:text-blue-500">Accueil</a>
                
                <?php if (!empty($_SESSION['user'])): ?>
                    <!-- Notifications -->
                        <div class="relative group">
                        
                        <!-- Panel des notifications -->
                        <!-- <div class="hidden absolute notification-panel bg-white rounded-md shadow-lg py-1 z-50 mt-2 group-hover:block">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <h3 class="text-sm font-medium">Notifications</h3>
                            </div>
                            <div class="overflow-y-auto max-h-96">
                                <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $notification): ?>
                                    <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                                        <div class="flex items-start">
                                            <span class="material-icons text-blue-500 mr-2"><?= htmlspecialchars($notification['icon'] ?? 'notifications') ?></span>
                                            <div>
                                                <p class="font-medium"><?= htmlspecialchars($notification['titre']) ?></p>
                                                <p><?= htmlspecialchars($notification['message']) ?></p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    <?= date('d/m/Y H:i', strtotime($notification['date_creation'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="px-4 py-3 text-sm text-gray-500">Aucune notification</p>
                                <?php endif; ?>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-200 text-center">
                                <a href="notifications.php" class="text-xs text-blue-600 hover:underline">Voir toutes les notifications</a>
                            </div>
                        </div> -->
                    </div>

                    <!-- Menu utilisateur -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <span class="hidden md:inline">
                                <?php 
                                    $prenom = $_SESSION['user']['prenom'] ?? null;
                                    $nom = $_SESSION['user']['nom'] ?? null;
                                    $affichageNom = $prenom ?: ($nom ?: 'Profil');
                                    echo htmlspecialchars($affichageNom);
                                    ?>

                            </span>
                           <img src="https://ui-avatars.com/api/?name=<?= 
                            urlencode(
                                (!empty($_SESSION['user']['prenom']) ? substr($_SESSION['user']['prenom'], 0, 1) : '') . 
                                (!empty($_SESSION['user']['nom']) ? substr($_SESSION['user']['nom'], 0, 1) : 'U')
                            ) ?>&background=3b82f6&color=fff" 
                            alt="Profil" class="w-8 h-8 rounded-full">
                                                            </button>
                        <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 group-hover:block">
                            <a href="<?= ($_SESSION['user']['role'] ?? 'client') === 'prestataire' ? 'dashboard-prestataire.php' : 'dashboard-client.php' ?>" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Tableau de bord
                            </a>
                            <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mon profil</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Déconnexion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Menu visiteur -->
                    <a href="login.php" class="hover:text-blue-500">Connexion</a>
                    <a href="register.php" class="hover:text-blue-500">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
</body>
</html>