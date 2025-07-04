<?php
include '../includes/db_connect.php';

// Fonctions pour les statistiques
function getRegistrationStats($pdo) {
    $stats = [
        'validated' => 0,
        'pending' => 0,
        'rejected' => 0
    ];
    
    $stmt = $pdo->query("SELECT etat_compte, COUNT(*) as count FROM prestataires GROUP BY etat_compte");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['etat_compte'] === 'Validé') $stats['validated'] = $row['count'];
        if ($row['etat_compte'] === 'En attente') $stats['pending'] = $row['count'];
        if ($row['etat_compte'] === 'Rejeté') $stats['rejected'] = $row['count'];
    }
    
    return $stats;
}

$stats = getRegistrationStats($pdo);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Administrateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white p-4">
            <h1 class="text-2xl font-bold mb-8">Headstart</h1>
            
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded" onclick="showDashboard()">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded" onclick="showPendingRegistrations()">
                            <i class="fas fa-user-clock mr-2"></i> Inscriptions en attente
                        </a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded" onclick="showServices()">
                            <i class="fas fa-concierge-bell mr-2"></i> Services
                        </a>
                    </li>
                    
                    <li>
                        <a href="../logout.php" class="block py-2 px-4 hover:bg-blue-700 rounded">
                            <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8 overflow-auto">
            <!-- Dashboard Content -->
            <div id="dashboard-content">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-blue-800">Tableau de bord</h2>
                    <p class="text-gray-600">Bienvenue dans votre espace administrateur</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Validés -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500">Comptes validés</h3>
                                <p class="text-2xl font-bold"><?= $stats['validated'] ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- En attente -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500">En attente</h3>
                                <p class="text-2xl font-bold"><?= $stats['pending'] ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Refusés -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600">
                                <i class="fas fa-times-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500">Comptes refusés</h3>
                                <p class="text-2xl font-bold"><?= $stats['rejected'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Autres statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Graphique -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-xl font-bold text-blue-800 mb-4">Activité récente</h3>
                        <div class="h-64 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                            <canvas id="registrationsChart"></canvas>
                        </div>
                    </div>

                    <!-- Dernières actions -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-xl font-bold text-blue-800 mb-4">Dernières actions</h3>
                        <ul class="space-y-4" id="recent-actions">
                            <!-- Rempli dynamiquement par JavaScript -->
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Inscriptions en attente -->
            <div id="pending-registrations-content" class="hidden">
                <?php include 'pending_registrations.php'; ?>
            </div>

            <!-- Gestion des services -->
            <div id="services-content" class="hidden">
                <?php include 'services.php'; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Navigation entre les sections
        function showDashboard() {
            document.getElementById('dashboard-content').classList.remove('hidden');
            document.getElementById('pending-registrations-content').classList.add('hidden');
            document.getElementById('services-content').classList.add('hidden');
        }

        function showPendingRegistrations() {
            document.getElementById('dashboard-content').classList.add('hidden');
            document.getElementById('pending-registrations-content').classList.remove('hidden');
            document.getElementById('services-content').classList.add('hidden');
        }

        function showServices() {
            document.getElementById('dashboard-content').classList.add('hidden');
            document.getElementById('pending-registrations-content').classList.add('hidden');
            document.getElementById('services-content').classList.remove('hidden');
        }

        // Initialisation du graphique
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des inscriptions
            const ctx = document.getElementById('registrationsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                    datasets: [{
                        label: 'Inscriptions',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Charger les dernières actions
            loadRecentActions();
        });

        // Charger les dernières actions
        function loadRecentActions() {
            axios.get('api/get_recent_actions.php')
                .then(response => {
                    const actionsList = document.getElementById('recent-actions');
                    actionsList.innerHTML = '';
                    
                    response.data.forEach(action => {
                        const li = document.createElement('li');
                        li.className = 'border-b pb-2';
                        li.innerHTML = `
                            <div class="flex justify-between">
                                <span class="font-medium">${action.type}</span>
                                <span class="text-sm text-gray-500">${action.time}</span>
                            </div>
                            <p class="text-sm text-gray-600">${action.details}</p>
                        `;
                        actionsList.appendChild(li);
                    });
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        }

        // Mettre à jour les statistiques
        function updateStats() {
            axios.get('api/get_stats.php')
                .then(response => {
                    document.querySelector('.stats-validated').textContent = response.data.validated;
                    document.querySelector('.stats-pending').textContent = response.data.pending;
                    document.querySelector('.stats-rejected').textContent = response.data.rejected;
                    loadRecentActions();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        }
    </script>
</body>
</html>