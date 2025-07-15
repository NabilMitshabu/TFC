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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .menu-item {
            transition: all 0.2s ease;
        }
        .menu-item:hover {
            transform: translateX(4px);
        }
        .favorite-card {
            transition: all 0.3s ease;
        }
        .favorite-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .animate-ping {
            animation: ping 0.5s cubic-bezier(0, 0, 0.2, 1);
        }
        @keyframes ping {
            0% { transform: scale(1); opacity: 1; }
            75%, 100% { transform: scale(1.5); opacity: 0; }
        }
        .content-section {
            transition: opacity 0.3s ease;
        }
        .hidden-section {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Header -->
    <?php require "portions/headerUsers.php" ?>

    <main class="min-h-screen flex pt-20 px-4">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-sm rounded-lg h-fit sticky top-24 mr-6 hidden md:block">
            <div class="p-6">
                <div class="flex flex-col items-center mb-8">
                    <div class="relative mb-4">
                        <?php if (!empty($user['photo_profil'])): ?>
                            <img src="<?= htmlspecialchars($user['photo_profil']) ?>" alt="Photo profil" class="rounded-full w-24 h-24 object-cover" />
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode(htmlspecialchars($user['prenom'] ?? '') . '+' . htmlspecialchars($user['nom'] ?? '')) ?>&background=2563eb&color=fff&size=128" alt="Photo profil" class="rounded-full w-24 h-24 object-cover" />
                        <?php endif; ?>
                    </div>
                    <h2 class="text-xl font-semibold"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . htmlspecialchars($user['nom'] ?? '')) ?></h2>
                    <p class="text-gray-500 text-sm">Client depuis <?= !empty($user['date_creation']) ? date('M Y', strtotime($user['date_creation'])) : 'date inconnue' ?></p>
                </div>

                <nav class="space-y-1">
                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 text-blue-600 font-medium menu-item dashboard-link">
                        <span class="material-icons">dashboard</span>
                        <span>Tableau de bord</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">person</span>
                        <span>Mon profil</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item" id="show-notifications">
                        <span class="material-icons">notifications</span>
                        <span>Notifications</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item favorites-link">
                        <span class="material-icons">favorite</span>
                        <span>Mes Favoris</span>
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full" id="sidebar-favorite-count">
                            <?= countUserFavorites($pdo, $_SESSION['user']['id']) ?>
                        </span>
                    </a>
                    
                    <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item messages-link">
                        <span class="material-icons">message</span>
                        <span>Messagerie</span>
                        <?php if ($unreadMessages > 0): ?>
                            <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full"><?= $unreadMessages ?></span>
                        <?php endif; ?>
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
            <div id="dashboard-content" class="content-section">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Tableau de bord</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 rounded-xl p-6 flex items-center">
                            <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                                <span class="material-icons">assignment</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Demandes en cours</p>
                                <p class="text-2xl font-bold"><?= $stats['current_requests'] ?></p>
                            </div>
                        </div>
                        
                        <div class="bg-green-50 rounded-xl p-6 flex items-center">
                            <div class="bg-green-100 text-green-600 p-3 rounded-full mr-4">
                                <span class="material-icons">check_circle</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Services terminés</p>
                                <p class="text-2xl font-bold"><?= $stats['completed_services'] ?></p>
                            </div>
                        </div>

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
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Informations personnelles</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Nom complet</p>
                                    <p class="font-medium"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium"><?= htmlspecialchars($user['email'] ?? 'Non défini') ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Téléphone</p>
                                    <p class="font-medium"><?= htmlspecialchars($user['telephone'] ?? 'Non défini') ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Localisation</p>
                                    <p class="font-medium"><?= htmlspecialchars(($user['commune'] ?? '') . ', ' . ($user['ville'] ?? '')) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications récentes -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Notifications récentes</h3>
                            <div class="space-y-4 max-h-64 overflow-y-auto" id="notifications-content">
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

                <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Évaluations en attente</h3>
                    
                    <?php if (!empty($pendingEvaluations)): ?>
                        <div class="space-y-4">
                            <?php foreach ($pendingEvaluations as $eval): ?>
                                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                    <div>
                                        <p class="font-medium"><?= htmlspecialchars($eval['service_nom']) ?></p>
                                        <p class="text-sm text-gray-600">Prestataire: <?= htmlspecialchars($eval['presta_nom']) ?></p>
                                        <p class="text-xs text-gray-500">Terminé le <?= date('d/m/Y', strtotime($eval['date_evaluation'])) ?></p>
                                    </div>
                                    <a href="evaluation.php?demande_id=<?= $eval['demande_id'] ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Évaluer
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">Aucune évaluation en attente</p>
                    <?php endif; ?>
                </div>

                <!-- Section pour les évaluations passées -->
                <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Vos dernières évaluations</h3>
                    
                    <?php if (!empty($pastEvaluations)): ?>
                        <div class="space-y-4">
                            <?php foreach ($pastEvaluations as $eval): ?>
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium"><?= htmlspecialchars($eval['service_nom']) ?></p>
                                            <p class="text-sm text-gray-600">Prestataire: <?= htmlspecialchars($eval['presta_nom']) ?></p>
                                        </div>
                                        <div class="flex items-center">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="material-icons text-sm">
                                                    <?= $i <= $eval['note'] ? 'star' : 'star_border' ?>
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($eval['commentaire'])): ?>
                                        <p class="mt-2 text-sm text-gray-700">"<?= htmlspecialchars($eval['commentaire']) ?>"</p>
                                    <?php endif; ?>
                                    <p class="mt-1 text-xs text-gray-500">Évalué le <?= date('d/m/Y', strtotime($eval['date_evaluation'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">Vous n'avez pas encore évalué de services</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section Favoris -->
            <div id="favorites-content" class="content-section hidden-section">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold flex items-center">
                            <i class="fas fa-heart text-red-500 mr-2"></i>
                            Mes Prestataires Favoris
                            <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full" id="favorite-count">
                                <?= countUserFavorites($pdo, $_SESSION['user']['id']) ?>
                            </span>
                        </h2>
                        <button id="refresh-favorites" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                    </div>

                    <div id="favorites-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach (getUserFavorites($pdo, $_SESSION['user']['id']) as $favorite): ?>
                            <div class="favorite-card-container group">
                                <div class="favorite-card bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 ease-in-out transform group-hover:-translate-y-1"
                                     data-prestataire-id="<?= $favorite['prestataire_id'] ?>">
                                    
                                    <div class="p-4 flex justify-between items-start">
                                        <div class="flex-1 pr-2">
                                            <div class="flex items-center mb-2">
                                                <h3 class="font-bold text-lg text-gray-800">
                                                    <?= htmlspecialchars($favorite['prenom'] . ' ' . $favorite['nom']) ?>
                                                </h3>
                                                <span class="ml-2 text-xs font-semibold px-2.5 py-0.5 rounded-full 
                                                      <?= $favorite['type_prestataire'] === 'entreprise' 
                                                          ? 'bg-blue-100 text-blue-800' 
                                                          : 'bg-green-100 text-green-800' ?>">
                                                         
                                                    <?= $unreadMessages = isset($unreadMessages) ? $unreadMessages : 0;
                                                    $favorite['type_prestataire'] === 'entreprise' ? 'Entreprise' : 'Indépendant' ?>
                                                </span>
                                            </div>
                                            
                                            <div class="flex items-center mt-1 text-sm text-gray-600 mb-3">
                                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                                <span class="font-medium">
                                                    <?= htmlspecialchars($favorite['ville'] . ', ' . $favorite['commune']) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="flex items-center mb-3">
                                                <div class="flex mr-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star text-<?= $i <= round($favorite['note_moyenne'] ?? 0) 
                                                            ? 'yellow-400' 
                                                            : 'gray-300' ?> text-sm"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="text-xs font-medium text-gray-500">
                                                    (<?= (int)($favorite['nombre_avis'] ?? 0) ?> avis)
                                                </span>
                                            </div>
                                            
                                            <?php if (!empty($favorite['services'])): ?>
                                                <div class="mt-3">
                                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Services proposés</span>
                                                    <p class="text-sm text-gray-700 mt-1 line-clamp-2">
                                                        <?= htmlspecialchars($favorite['services']) ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button class="favorite-btn text-red-400 hover:text-red-600 p-2 transition-colors duration-200"
                                                data-prestataire-id="<?= $favorite['prestataire_id'] ?>">
                                            <i class="fas fa-heart text-xl"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 flex justify-end border-t border-gray-200">
                                        <a href="profilPresta.php?id=<?= $favorite['prestataire_id'] ?>" 
                                           class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center transition-colors duration-200">
                                            Voir le profil complet
                                            <i class="fas fa-arrow-right ml-2 text-xs transition-transform duration-200 group-hover:translate-x-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty(getUserFavorites($pdo, $_SESSION['user']['id']))): ?>
                            <div class="col-span-full text-center py-8">
                                <i class="far fa-heart text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">Vous n'avez aucun prestataire favori</p>
                                <p class="text-sm text-gray-400 mt-1">Cliquez sur ♡ pour ajouter des prestataires à vos favoris</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <!-- Section Messagerie -->
           <div id="messages-content" class="content-section hidden-section">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Messagerie</h2>
                
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Liste des conversations -->
                    <div class="md:w-1/3 border-r border-gray-200 pr-4">
                        <div class="relative mb-4">
                            <input type="text" placeholder="Rechercher une conversation..." class="w-full p-2 border rounded-lg pl-10">
                            <span class="material-icons absolute left-3 top-2.5 text-gray-400">search</span>
                        </div>
                        
                        <div class="space-y-2 max-h-[600px] overflow-y-auto" id="conversations-list">
                            <!-- Les conversations seront chargées ici via AJAX -->
                        </div>
                    </div>
                    
                    <!-- Zone de discussion -->
                    <div class="md:w-2/3">
                        <div id="chat-container" class="hidden">
                            <div class="flex items-center border-b border-gray-200 pb-3 mb-4">
                                <img id="current-chat-photo" src="" alt="" class="w-10 h-10 rounded-full object-cover mr-3">
                                <h3 id="current-chat-name" class="font-medium"></h3>
                            </div>
                            
                            <div id="messages-container" class="h-[400px] overflow-y-auto mb-4 space-y-3 p-2">
                                <!-- Les messages seront chargés ici via AJAX -->
                            </div>
                            
                            <form id="message-form" class="flex gap-2">
                                <input type="hidden" id="recipient-id" name="recipient_id">
                                <input type="text" name="message" placeholder="Écrivez un message..." 
                                    class="flex-1 border rounded-lg p-2" required>
                                <button type="submit" class="bg-blue-600 text-white p-2 rounded-lg">
                                    <span class="material-icons">send</span>
                                </button>
                            </form>
                        </div>
                        
                        <div id="no-chat-selected" class="flex flex-col items-center justify-center h-[400px] text-gray-500">
                            <span class="material-icons text-4xl mb-2">forum</span>
                            <p>Sélectionnez une conversation pour commencer à discuter</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

  <script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const userId = <?= json_encode($_SESSION['user']['id']) ?>;

    // -- Affichage par défaut
    document.getElementById('dashboard-content').classList.remove('hidden-section');

    // -- Onglets (Tableau de bord / Favoris / Messagerie)
    document.querySelector('.dashboard-link')?.addEventListener('click', function (e) {
        e.preventDefault();
        toggleSections('dashboard');
        updateActiveLink(this);
    });

    document.querySelector('.favorites-link')?.addEventListener('click', function (e) {
        e.preventDefault();
        toggleSections('favorites');
        updateActiveLink(this);
    });

    document.querySelector('.messages-link')?.addEventListener('click', function (e) {
        e.preventDefault();
        toggleSections('messages');
        updateActiveLink(this);
        loadConversations();
    });

    function toggleSections(section) {
        document.getElementById('dashboard-content')?.classList.add('hidden-section');
        document.getElementById('favorites-content')?.classList.add('hidden-section');
        document.getElementById('messages-content')?.classList.add('hidden-section');

        document.getElementById(section + '-content')?.classList.remove('hidden-section');
    }

    function updateActiveLink(activeLink) {
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('bg-blue-50', 'text-blue-600');
            item.classList.add('text-gray-600', 'hover:bg-gray-50');
        });
        activeLink.classList.add('bg-blue-50', 'text-blue-600');
        activeLink.classList.remove('text-gray-600', 'hover:bg-gray-50');
    }

    // -- Gestion des favoris
    document.addEventListener('click', async function (e) {
        if (e.target.closest('.favorite-btn')) {
            const btn = e.target.closest('.favorite-btn');
            const prestataireId = btn.dataset.prestataireId;
            const card = btn.closest('.favorite-card');
            const heartIcon = btn.querySelector('i');

            try {
                const response = await fetch('controllers/favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ prestataire_id: prestataireId })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    heartIcon.classList.add('animate-ping');
                    setTimeout(() => heartIcon.classList.remove('animate-ping'), 500);

                    document.getElementById('favorite-count').textContent = data.total_favorites;
                    document.getElementById('sidebar-favorite-count').textContent = data.total_favorites;

                    if (data.action === 'removed' && card) {
                        card.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                        setTimeout(() => card.remove(), 300);

                        if (data.total_favorites === 0) {
                            document.getElementById('favorites-container').innerHTML = `
                                <div class="col-span-full text-center py-8">
                                    <i class="far fa-heart text-gray-300 text-4xl mb-3"></i>
                                    <p class="text-gray-500">Vous n'avez aucun prestataire favori</p>
                                    <p class="text-sm text-gray-400 mt-1">Cliquez sur ♡ pour ajouter des prestataires à vos favoris</p>
                                </div>
                            `;
                        }
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            }
        }
    });

    // -- Actualisation des favoris
    document.getElementById('refresh-favorites')?.addEventListener('click', async function () {
        try {
            const response = await fetch('controllers/favorites.php', {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();

            if (data.status === 'success') {
                location.reload();
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    });

    // -- Notifications
    document.getElementById('show-notifications')?.addEventListener('click', function (event) {
        event.preventDefault();
        fetch('notifications.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('notifications-content').innerHTML = data;
            })
            .catch(error => {
                console.error('Erreur lors du chargement des notifications:', error);
            });
    });

    // -- Chargement des conversations
    function loadConversations() {
        fetch('../controllers/get_conversations.php')
            .then(response => response.json())
            .then(conversations => {
                const container = document.getElementById('conversations-list');
                container.innerHTML = '';

                if (!conversations.length) {
                    container.innerHTML = '<p class="text-gray-500 p-3">Aucune conversation</p>';
                    return;
                }

                conversations.forEach(conv => {
                    const nom = `${conv.contact_prenom} ${conv.contact_nom}`;
                    const photo = conv.contact_photo || 'https://cdn-icons-png.flaticon.com/512/219/219969.png';

                    const convItem = document.createElement('div');
                    convItem.className = 'p-3 rounded-lg hover:bg-gray-50 cursor-pointer flex items-center conversation-item';
                    convItem.dataset.prestataireId = conv.contact_id;

                    convItem.innerHTML = `
                        <img src="${photo}" alt="${nom}" class="w-10 h-10 rounded-full object-cover mr-3">
                        <div class="flex-1">
                            <h4 class="font-medium">${nom}</h4>
                            <p class="text-sm text-gray-500 truncate">${conv.last_message || ''}</p>
                        </div>
                        ${conv.unread_count > 0
                            ? `<span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">${conv.unread_count}</span>`
                            : `<span class="text-xs text-gray-400">${new Date(conv.last_message_date).toLocaleDateString()}</span>`}
                    `;

                    convItem.addEventListener('click', function () {
                        document.getElementById('current-chat-photo').src = photo;
                        document.getElementById('current-chat-name').textContent = nom;
                        document.getElementById('recipient-id').value = conv.contact_id;

                        document.getElementById('chat-container').classList.remove('hidden');
                        document.getElementById('no-chat-selected').classList.add('hidden');

                        loadMessages(conv.contact_id);
                    });

                    container.appendChild(convItem);
                });
            })
            .catch(err => {
                console.error(err);
                document.getElementById('conversations-list').innerHTML = '<p class="text-red-500 p-3">Erreur de chargement</p>';
            });
    }

    // -- Charger les messages
    function loadMessages(prestaId) {
        fetch(`../controllers/get_messages.php?recipient_id=${prestaId}`)
            .then(response => response.json())
            .then(messages => {
                const container = document.getElementById('messages-container');
                container.innerHTML = '';

                messages.forEach(msg => {
                    const isSender = msg.sender_id == userId;
                    const messageClass = isSender ? 'bg-blue-100 ml-auto' : 'bg-gray-100 mr-auto';

                    const messageDiv = document.createElement('div');
                    messageDiv.className = `max-w-[70%] p-3 rounded-lg ${messageClass}`;
                    messageDiv.innerHTML = `
                        <p class="text-gray-800">${msg.contenu}</p>
                        <p class="text-xs text-gray-500 mt-1 text-right">
                            ${new Date(msg.date_envoi).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </p>
                    `;

                    container.appendChild(messageDiv);
                });

                container.scrollTop = container.scrollHeight;
            });
    }

    // -- Envoi message
    document.getElementById('message-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('sender_id', userId);

        const response = await fetch('../controllers/send_message.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            this.reset();
            loadMessages(document.getElementById('recipient-id').value);
            loadConversations();
        }
    });
});
</script>

</body>
</html>