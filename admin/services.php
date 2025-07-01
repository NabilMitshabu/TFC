<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Services</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php
    require __DIR__ . '/../includes/db_connect.php';
    
    if (!isset($pdo)) {
        die("Erreur de connexion à la base de données");
    }
    ?>
    
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-blue-700 text-white shadow-lg">
            <div class="container mx-auto px-4 py-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-cogs mr-3"></i>
                        Administration des Services
                    </h1>
                    <nav>
                        <a href="#" class="bg-blue-800 px-4 py-2 rounded-lg hover:bg-blue-900 transition duration-200">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <!-- Formulaire d'ajout -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 border border-blue-100">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-plus-circle mr-3"></i>
                        Ajouter un Nouveau Service
                    </h2>
                </div>
                <div class="p-6">
                    <form id="add-service-form" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag mr-2 text-blue-600"></i>Nom du Service
                                </label>
                                <input type="text" id="nom" name="nom" required 
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            </div>
                            <div>
                                <label for="icone" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-icons mr-2 text-blue-600"></i>Code SVG de l'icône
                                </label>
                                <textarea id="icone" name="icone" rows="3" required
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"></textarea>
                                <p class="mt-2 text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-blue-400"></i>Coller le code SVG de l'icône (ex: &lt;path.../&gt;)
                                </p>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="flex items-center bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Ajouter le Service
                            </button>
                        </div>
                        <div id="form-message" class="hidden mt-4 p-4 rounded"></div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des services -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-blue-100">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-list-alt mr-3"></i>
                        Services Disponibles
                    </h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        <i class="fas fa-tag mr-2"></i>Nom
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        <i class="fas fa-image mr-2"></i>Icône
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        <i class="fas fa-toggle-on mr-2"></i>Statut
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        <i class="fas fa-cogs mr-2"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                        try {
                                        $query = $pdo->query("SELECT * FROM servicesacc ORDER BY nom");
                                        while ($service = $query->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                    <tr class="hover:bg-blue-50 transition duration-150" data-id="<?= $service['id'] ?>">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            <?= htmlspecialchars($service['nom']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center justify-center bg-blue-100 rounded-full w-10 h-10">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                                    <?= $service['icone'] ?>
                                                </svg>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap status-cell">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $service['est_actif'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $service['est_actif'] ? '<i class="fas fa-eye mr-1"></i> Actif' : '<i class="fas fa-eye-slash mr-1"></i> Masqué' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap action-links">
                                            <?php if ($service['est_actif']): ?>
                                                <a href="#" onclick="handleServiceAction('masquer', <?= $service['id'] ?>); return false;" 
                                                class="inline-flex items-center text-yellow-600 hover:text-yellow-800 transition duration-200"
                                                title="Masquer ce service">
                                                    <i class="fas fa-toggle-off mr-1"></i> Masquer
                                                </a>
                                            <?php else: ?>
                                                <a href="#" onclick="handleServiceAction('afficher', <?= $service['id'] ?>); return false;" 
                                                class="inline-flex items-center text-blue-600 hover:text-blue-800 transition duration-200"
                                                title="Afficher ce service">
                                                    <i class="fas fa-toggle-on mr-1"></i> Afficher
                                                </a>
                                            <?php endif; ?>
                                            <a href="#" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer définitivement ce service?')) { handleServiceAction('supprimer', <?= $service['id'] ?>); } return false;" 
                                            class="inline-flex items-center text-red-600 hover:text-red-800 transition duration-200"
                                            title="Supprimer ce service">
                                                <i class="fas fa-trash-alt mr-1"></i> Supprimer
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='4' class='px-6 py-4 text-center text-red-600 bg-red-50'><i class='fas fa-exclamation-triangle mr-2'></i>Erreur lors du chargement des services: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-100 border-t border-gray-200 mt-12">
            <div class="container mx-auto px-4 py-6 text-center text-gray-600 text-sm">
                <p>© 2023 Administration des Services. Tous droits réservés.</p>
            </div>
        </footer>
    </div>

    <!-- Petite animation pour les boutons -->
    <script>
        document.querySelectorAll('a, button').forEach(el => {
            el.addEventListener('mouseenter', () => {
                el.classList.add('transform', 'hover:-translate-y-0.5');
            });
            el.addEventListener('mouseleave', () => {
                el.classList.remove('transform', 'hover:-translate-y-0.5');
            });
        });


        document.getElementById('add-service-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const messageDiv = document.getElementById('form-message');
    
    // Afficher un indicateur de chargement
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Ajout en cours...';
    submitButton.disabled = true;
    
    axios.post('process_services.php', formData)
        .then(response => {
            if (response.data.success) {
                // Afficher un message de succès
                messageDiv.className = 'bg-green-100 text-green-800 mt-4 p-4 rounded';
                messageDiv.innerHTML = `<i class="fas fa-check-circle mr-2"></i> ${response.data.message}`;
                messageDiv.classList.remove('hidden');
                
                // Réinitialiser le formulaire
                form.reset();
                
                // Ajouter le nouveau service au tableau (sans recharger)
                addServiceToTable(response.data.service);
            } else {
                // Afficher un message d'erreur
                messageDiv.className = 'bg-red-100 text-red-800 mt-4 p-4 rounded';
                messageDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i> ${response.data.message}`;
                messageDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            messageDiv.className = 'bg-red-100 text-red-800 mt-4 p-4 rounded';
            messageDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i> Une erreur est survenue lors de l'ajout du service`;
            messageDiv.classList.remove('hidden');
        })
        .finally(() => {
            // Réinitialiser le bouton
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
            
            // Cacher le message après 5 secondes
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 5000);
        });
});

function addServiceToTable(service) {
    const tbody = document.querySelector('tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'hover:bg-blue-50 transition duration-150';
    newRow.dataset.id = service.id;
    
    newRow.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
            ${service.nom}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center justify-center bg-blue-100 rounded-full w-10 h-10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 text-blue-600">
                    ${service.icone}
                </svg>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap status-cell">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                <i class="fas fa-eye mr-1"></i> Actif
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap action-links">
            <a href="#" onclick="handleServiceAction('masquer', ${service.id}); return false;" 
               class="inline-flex items-center text-yellow-600 hover:text-yellow-800 transition duration-200"
               title="Masquer ce service">
                <i class="fas fa-toggle-off mr-1"></i> Masquer
            </a>
            <a href="#" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer définitivement ce service?')) { handleServiceAction('supprimer', ${service.id}); } return false;" 
               class="inline-flex items-center text-red-600 hover:text-red-800 transition duration-200"
               title="Supprimer ce service">
                <i class="fas fa-trash-alt mr-1"></i> Supprimer
            </a>
        </td>
    `;
    }
    </script>
</body>
</html>