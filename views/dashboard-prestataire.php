<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Prestataire</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
        .tab-section {
            display: none;
        }
        .tab-section.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <header class="bg-white shadow-sm fixed w-full top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-600">ClicService</h1>
            <nav class="flex items-center space-x-4">
                <button class="text-gray-600 hover:text-blue-500 transition">
                    <span class="material-icons">settings</span>
                </button>
                <button class="text-gray-600 hover:text-blue-500 transition">
                    <span class="material-icons">notifications</span>
                </button>
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">P</div>
            </nav>
        </div>
    </header>

    <main class="min-h-screen flex pt-20 px-4">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-sm rounded-lg h-fit sticky top-24 mr-6 hidden md:block">
            <div class="p-6">
                <div class="flex flex-col items-center mb-8">
                    <div class="relative mb-4">
                        <img src="https://ui-avatars.com/api/?name=Entreprise+Services&background=2563eb&color=fff&size=128" alt="Photo profil" class="rounded-full w-24 h-24 object-cover profile-shadow" />
                        <button class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition transform hover:scale-105">
                            <span class="material-icons text-sm">edit</span>
                        </button>
                    </div>
                    <h2 class="text-xl font-semibold">Entreprise Services Pro</h2>
                    <p class="text-gray-500 text-sm">presta@example.com</p>
                </div>

                <nav class="space-y-1">
                    <a href="#" onclick="showTab('accueil')" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 text-blue-600 font-medium menu-item">
                        <span class="material-icons">home</span>
                        <span>Accueil</span>
                    </a>
                    <a href="#" onclick="showTab('demandes')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">mail</span>
                        <span>Demandes</span>
                    </a>
                    <a href="#" onclick="showTab('taches')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">calendar_today</span>
                        <span>Tâches</span>
                    </a>
                    <a href="#" onclick="showTab('profil')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">person</span>
                        <span>Profil</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Accueil -->
            <section id="accueil" class="tab-section active">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Tableau de bord</h2>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Bienvenue</h3>
                        <p class="text-gray-600">Bienvenue sur votre espace professionnel ClicService.</p>
                    </div>
                </div>
            </section>

            <!-- Demandes -->
            <section id="demandes" class="tab-section">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Demandes reçues</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Heure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Jean Client</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        jean@example.com<br>
                                        +243 900000001
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Réparer fuite lavabo</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-06-05 à 10h</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Lubumbashi, Centre-ville</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 space-x-2">
                                        <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition">Accepter</button>
                                        <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">Décliner</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Tâches -->
            <section id="taches" class="tab-section">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Évolution des tâches</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valider</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Claire Service</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-06-07</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <select class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option>En cours</option>
                                            <option>Fini</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition">Valider</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Profil -->
            <section id="profil" class="tab-section">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Mon Profil</h2>
                        <button class="mt-3 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center space-x-2">
                            <span class="material-icons">edit</span>
                            <span>Modifier le profil</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Informations personnelles</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Nom</p>
                                    <p class="font-medium">Entreprise Services Pro</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium">presta@example.com</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Téléphone</p>
                                    <p class="font-medium">+243 900000002</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Localisation</p>
                                    <p class="font-medium">Lubumbashi, RD Congo</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Statistiques</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Tâches complétées</p>
                                    <p class="font-medium">24</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Évaluation moyenne</p>
                                    <p class="font-medium">4.7/5</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Membre depuis</p>
                                    <p class="font-medium">Janvier 2023</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Statut du compte</p>
                                    <p class="font-medium text-green-600">Actif</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Mobile bottom navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 z-50">
        <div class="flex justify-around">
            <a href="#" onclick="showTab('accueil')" class="flex flex-col items-center justify-center p-3 text-blue-600">
                <span class="material-icons">home</span>
                <span class="text-xs mt-1">Accueil</span>
            </a>
            <a href="#" onclick="showTab('demandes')" class="flex flex-col items-center justify-center p-3 text-gray-500">
                <span class="material-icons">mail</span>
                <span class="text-xs mt-1">Demandes</span>
            </a>
            <a href="#" onclick="showTab('taches')" class="flex flex-col items-center justify-center p-3 text-gray-500">
                <span class="material-icons">calendar_today</span>
                <span class="text-xs mt-1">Tâches</span>
            </a>
            <a href="#" onclick="showTab('profil')" class="flex flex-col items-center justify-center p-3 text-gray-500">
                <span class="material-icons">person</span>
                <span class="text-xs mt-1">Profil</span>
            </a>
        </div>
    </div>

    <script>
        function showTab(id) {
            // Hide all tabs
            document.querySelectorAll('.tab-section').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(id).classList.add('active');
            
            // Update active state in sidebar
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'text-blue-600');
                item.classList.add('text-gray-600', 'hover:bg-gray-50');
            });
            
            const activeItem = document.querySelector(`[onclick="showTab('${id}')"]`);
            if (activeItem) {
                activeItem.classList.remove('text-gray-600', 'hover:bg-gray-50');
                activeItem.classList.add('bg-blue-50', 'text-blue-600');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize chart
            const ctx = document.getElementById('statusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['En attente', 'En cours', 'Fini'],
                    datasets: [{
                        data: [3, 5, 2],
                        backgroundColor: ['#F59E0B', '#3B82F6', '#10B981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 20
                            }
                        }
                    }
                }
            });
            
            // Set first tab as active by default
            showTab('accueil');
        });
    </script>
</body>
</html>