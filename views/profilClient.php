<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../controllers/functions.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: signInClient.php');
    exit;
}

// Récupération des données utilisateur
$user = getUserData($pdo, $_SESSION['user']['id']);

// Vérification que l'utilisateur existe
if (empty($user)) {
    header('Location: signInClient.php');
    exit;
}



// Génération du token CSRF
$csrfToken = generateCsrfToken();

// Récupération des statistiques
$stats = getClientStats($pdo, $_SESSION['user']['id']);

// Récupération des notifications
$notifications = getNotifications($pdo, $_SESSION['user']['id']);

// Comptage des notifications non lues
$unreadNotifications = countUnreadNotifications($pdo, $_SESSION['user']['id']);

// Récupération des messages récents
$recentMessages = getRecentMessages($pdo, $_SESSION['user']['id']);

// Comptage des messages non lus
$unreadMessages = countUnreadMessages($pdo, $_SESSION['user']['id']);

// Récupération des évaluations à faire
$pendingEvaluations = getPendingEvaluations($pdo, $_SESSION['user']['id']);

// Récupération des évaluations précédentes
$pastEvaluations = getPastEvaluations($pdo, $_SESSION['user']['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Espace Client – ClicService</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .material-icons {
            font-family: 'Material Icons';
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            vertical-align: middle;
        }
        .menu-item {
            transition: all 0.2s ease;
        }
        .menu-item:hover {
            transform: translateX(4px);
        }
        .profile-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .rating-star {
            cursor: pointer;
            transition: all 0.2s;
        }
        .rating-star:hover {
            transform: scale(1.2);
        }
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <header class="bg-white shadow-sm fixed w-full top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-600">ClicService</h1>
            <nav class="flex items-center space-x-4">
                <button class="text-gray-600 hover:text-blue-500 transition relative" id="notifications-btn">
                    <span class="material-icons">notifications</span>
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?= $unreadNotifications ?></span>
                    <?php endif; ?>
                </button>
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                    <?= isset($user['prenom'], $user['nom']) ? 
                        htmlspecialchars(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) : 
                        '?' ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="min-h-screen flex pt-20 px-4">
        <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-sm rounded-lg h-fit sticky top-24 mr-6 hidden md:block">
        <div class="p-6">
            <div class="flex flex-col items-center mb-8">
                <div class="relative mb-4">
                    <?php if (!empty($user['photo_profil'])): ?>
                        <img src="<?= htmlspecialchars($user['photo_profil']) ?>" alt="Photo profil" class="rounded-full w-24 h-24 object-cover profile-shadow" />
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(htmlspecialchars($user['prenom'] ?? '') . '+' . htmlspecialchars($user['nom'] ?? '')) ?>&background=2563eb&color=fff&size=128" alt="Photo profil" class="rounded-full w-24 h-24 object-cover profile-shadow" />
                    <?php endif; ?>
                </div>
                <h2 class="text-xl font-semibold"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . htmlspecialchars($user['nom'] ?? '')) ?></h2>
                <p class="text-gray-500 text-sm">Client depuis <?= !empty($user['date_creation']) ? date('M Y', strtotime($user['date_creation'])) : 'date inconnue' ?></p>
            </div>

            <nav class="space-y-1">
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 text-blue-600 font-medium menu-item">
                    <span class="material-icons">dashboard</span>
                    <span>Tableau de bord</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                    <span class="material-icons">person</span>
                    <span>Mon profil</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                    <span class="material-icons">message</span>
                    <span>Messages</span>
                    <?php if ($unreadMessages > 0): ?>
                        <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full"><?= $unreadMessages ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                    <span class="material-icons">assignment</span>
                    <span>Mes demandes</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                    <span class="material-icons">history</span>
                    <span>Historique</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                    <span class="material-icons">favorite</span>
                    <span>Favoris</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                    <span class="material-icons">payments</span>
                    <span>Paiements</span>
                </a>
                <a href="../controllers/logout.php" class="flex items-center space-x-3 p-3 rounded-lg text-red-500 hover:bg-red-50 font-medium menu-item">
                    <span class="material-icons">logout</span>
                    <span>Déconnexion</span>
                </a>
            </nav>
        </div>
    </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Section Tableau de bord -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Tableau de bord</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Demande en cours -->
                    <div class="bg-blue-50 rounded-xl p-6 flex items-center">
                        <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                            <span class="material-icons">assignment</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Demandes en cours</p>
                            <p class="text-2xl font-bold"><?= $stats['current_requests'] ?></p>
                        </div>
                    </div>
                    
                    <!-- Services terminés -->
                    <div class="bg-green-50 rounded-xl p-6 flex items-center">
                        <div class="bg-green-100 text-green-600 p-3 rounded-full mr-4">
                            <span class="material-icons">check_circle</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Services terminés</p>
                            <p class="text-2xl font-bold"><?= $stats['completed_services'] ?></p>
                        </div>
                    </div>
                    
                    <!-- Prestataires favoris -->
                    <div class="bg-purple-50 rounded-xl p-6 flex items-center">
                        <div class="bg-purple-100 text-purple-600 p-3 rounded-full mr-4">
                            <span class="material-icons">star</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Prestataires favoris</p>
                            <p class="text-2xl font-bold"><?= $stats['favorite_providers'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informations personnelles -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Informations personnelles</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Nom complet</p>
                                <p class="font-medium">
                                    <?= isset($user['prenom'], $user['nom']) ? 
                                        htmlspecialchars($user['prenom'] . ' ' . $user['nom']) : 
                                        'Non défini' ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium"><?= isset($user['email']) ? htmlspecialchars($user['email']) : 'Non défini' ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Téléphone</p>
                                <p class="font-medium"><?= isset($user['telephone']) ? htmlspecialchars($user['telephone']) : 'Non défini' ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Localisation</p>
                                <p class="font-medium">
                                    <?= (isset($user['commune']) ? htmlspecialchars($user['commune']) : '') . 
                                       (isset($user['ville'], $user['commune']) ? ', ' : '') . 
                                       (isset($user['ville']) ? htmlspecialchars($user['ville']) : '') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications récentes -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Notifications récentes</h3>
                        <div class="space-y-4 max-h-64 overflow-y-auto">
                            <?php foreach ($notifications as $notification): ?>
                            <div class="flex items-start space-x-3 p-3 bg-white rounded-lg">
                                <div class="bg-blue-100 text-blue-600 p-2 rounded-full">
                                    <span class="material-icons text-sm"><?= htmlspecialchars($notification['icon'] ?? 'notifications') ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-sm"><?= htmlspecialchars($notification['titre'] ?? 'Notification') ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?= timeAgo($notification['date_creation'] ?? 'now') ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($notifications)): ?>
                                <p class="text-gray-500 text-sm">Aucune notification récente</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Évaluation -->
            <?php if (!empty($pendingEvaluations)): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Évaluer un service récent</h2>
                
                <?php foreach ($pendingEvaluations as $evaluation): ?>
                <div class="bg-blue-50 rounded-xl p-6 mb-6">
                    <form action="submit_evaluation.php" method="POST" class="evaluation-form">
                        <input type="hidden" name="demande_id" value="<?= $evaluation['demande_id'] ?>">
                        <input type="hidden" name="prestataire_id" value="<?= $evaluation['prestataire_id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        
                        <div class="flex flex-col md:flex-row md:items-center">
                            <div class="flex items-center mb-4 md:mb-0 md:w-1/3">
                                <img src="<?= getProfileImage($evaluation['prestataire_photo'] ?? null, $evaluation['prestataire_nom'] ?? '') ?>" 
                                     alt="Prestataire" class="rounded-full w-12 h-12 mr-4">
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($evaluation['prestataire_nom'] ?? 'Prestataire') ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?= htmlspecialchars($evaluation['service_nom'] ?? 'Service') ?> • 
                                        <?= isset($evaluation['date_service']) ? date('d M Y', strtotime($evaluation['date_service'])) : 'Date inconnue' ?>
                                    </p>
                                </div>
                            </div>
                            <div class="md:w-2/3">
                                <p class="text-gray-700 mb-3">Comment était votre expérience avec ce prestataire ?</p>
                                <div class="flex items-center mb-4 rating-stars" data-rating="0">
                                    <input type="hidden" name="note" value="0">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="material-icons rating-star text-3xl text-gray-300" data-value="<?= $i ?>">star</span>
                                    <?php endfor; ?>
                                </div>
                                <textarea name="commentaire" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Ajoutez un commentaire (optionnel)"></textarea>
                                <button type="submit" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                    Envoyer l'évaluation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
                
                <?php if (!empty($pastEvaluations)): ?>
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Vos évaluations précédentes</h3>
                <div class="space-y-4">
                    <?php foreach ($pastEvaluations as $evaluation): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center">
                                <img src="<?= getProfileImage($evaluation['prestataire_photo'] ?? null, $evaluation['prestataire_nom'] ?? '') ?>" 
                                     alt="Prestataire" class="rounded-full w-10 h-10 mr-3">
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($evaluation['prestataire_nom'] ?? 'Prestataire') ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?= htmlspecialchars($evaluation['service_nom'] ?? 'Service') ?> • 
                                        <?= isset($evaluation['date_evaluation']) ? date('d M Y', strtotime($evaluation['date_evaluation'])) : 'Date inconnue' ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="material-icons text-yellow-400">
                                        <?= $i <= ($evaluation['note'] ?? 0) ? 'star' : ($i == ceil($evaluation['note'] ?? 0) && (($evaluation['note'] ?? 0) % 1 > 0) ? 'star_half' : 'star') ?>
                                    </span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if (!empty($evaluation['commentaire'])): ?>
                        <p class="text-gray-700">"<?= htmlspecialchars($evaluation['commentaire']) ?>"</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Section Messages -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Messages récents</h2>
                
                <div class="space-y-4">
                    <?php foreach ($recentMessages as $message): ?>
                    <a href="messages.php?conversation=<?= $message['sender_id'] ?? '' ?>" class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer block">
                        <img src="<?= getProfileImage($message['sender_photo'] ?? null, $message['sender_name'] ?? '') ?>" 
                             alt="<?= htmlspecialchars($message['sender_name'] ?? 'Expéditeur') ?>" class="rounded-full w-12 h-12 mr-4">
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <p class="font-medium"><?= htmlspecialchars($message['sender_name'] ?? 'Expéditeur') ?></p>
                                <p class="text-xs text-gray-500"><?= timeAgo($message['date_envoi'] ?? 'now') ?></p>
                            </div>
                            <p class="text-sm text-gray-600 mb-1"><?= htmlspecialchars(truncate($message['contenu'] ?? '', 50)) ?></p>
                            <?php if (isset($message['is_read']) && !$message['is_read']): ?>
                                <span class="inline-block bg-blue-500 text-white text-xs px-2 py-1 rounded-full">Nouveau</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($recentMessages)): ?>
                        <p class="text-gray-500">Aucun message récent</p>
                    <?php endif; ?>
                </div>
                
                <a href="messages.php" class="mt-6 w-full md:w-auto bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg font-medium transition inline-block text-center">
                    Voir tous les messages
                </a>
            </div>
        </div>
    </main>

    <!-- Mobile bottom navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 z-50">
        <div class="flex justify-around">
            <a href="#" class="flex flex-col items-center justify-center p-3 text-blue-600">
                <span class="material-icons">dashboard</span>
                <span class="text-xs mt-1">Accueil</span>
            </a>
            <a href="#" class="flex flex-col items-center justify-center p-3 text-gray-500 relative">
                <span class="material-icons">notifications</span>
                <span class="text-xs mt-1">Alertes</span>
                <?php if ($unreadNotifications > 0): ?>
                    <span class="absolute top-1 right-4 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?= $unreadNotifications ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="flex flex-col items-center justify-center p-3 text-gray-500 relative">
                <span class="material-icons">message</span>
                <span class="text-xs mt-1">Messages</span>
                <?php if ($unreadMessages > 0): ?>
                    <span class="absolute top-1 right-4 bg-blue-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?= $unreadMessages ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="flex flex-col items-center justify-center p-3 text-gray-500">
                <span class="material-icons">person</span>
                <span class="text-xs mt-1">Profil</span>
            </a>
        </div>
    </div>

    <!-- Notifications Panel (hidden by default) -->
    <div id="notifications-panel" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="absolute top-16 right-4 w-80 bg-white rounded-lg shadow-xl">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold">Notifications</h3>
                <button id="close-notifications" class="text-gray-500 hover:text-gray-700">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <?php foreach ($notifications as $notification): ?>
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer">
                    <p class="font-medium text-sm"><?= htmlspecialchars($notification['titre'] ?? 'Notification') ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= timeAgo($notification['date_creation'] ?? 'now') ?></p>
                </div>
                <?php endforeach; ?>
                <?php if (empty($notifications)): ?>
                <div class="p-4 text-center text-gray-500">
                    Aucune notification
                </div>
                <?php endif; ?>
            </div>
            <div class="p-3 bg-gray-50 text-center">
                <a href="notifications.php" class="text-sm text-blue-600 hover:underline">Voir toutes les notifications</a>
            </div>
        </div>
    </div>

    <script>
        // Gestion des notifications
        const notificationsBtn = document.getElementById('notifications-btn');
        const notificationsPanel = document.getElementById('notifications-panel');
        const closeNotifications = document.getElementById('close-notifications');
        
        notificationsBtn.addEventListener('click', function() {
            notificationsPanel.classList.toggle('hidden');
        });
        
        closeNotifications.addEventListener('click', function() {
            notificationsPanel.classList.add('hidden');
        });

        // Système d'évaluation par étoiles
        document.querySelectorAll('.rating-stars').forEach(starsContainer => {
            const stars = starsContainer.querySelectorAll('.rating-star');
            const hiddenInput = starsContainer.querySelector('input[name="note"]');
            let currentRating = parseInt(hiddenInput.value);
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.getAttribute('data-value'));
                    currentRating = rating;
                    hiddenInput.value = rating;
                    
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.textContent = 'star';
                            s.classList.remove('text-gray-300');
                            s.classList.add('text-yellow-400');
                        } else {
                            s.textContent = 'star';
                            s.classList.remove('text-yellow-400');
                            s.classList.add('text-gray-300');
                        }
                    });
                });
                
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.getAttribute('data-value'));
                    
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.textContent = 'star';
                            s.classList.remove('text-gray-300');
                            s.classList.add('text-yellow-400');
                        }
                    });
                });
                
                star.addEventListener('mouseout', function() {
                    stars.forEach((s, index) => {
                        if (index >= currentRating) {
                            s.textContent = 'star';
                            s.classList.remove('text-yellow-400');
                            s.classList.add('text-gray-300');
                        }
                    });
                });
            });
        });

        // Animation pour les éléments du menu
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(4px)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });

        // Gestion CSRF pour les formulaires AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            // Intercepter les formulaires
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (form.method.toLowerCase() === 'post') {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = 'csrf_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                    }
                });
            });
        });
    </script>
</body>
</html>