<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Services</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php
    // Inclure le fichier de connexion à la base de données
    require __DIR__ . '/../includes/db_connect.php';
    
    // Vérifier si la connexion PDO est établie
    if (!isset($pdo)) {
        die("Erreur de connexion à la base de données");
    }
    ?>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Gestion des Services</h1>
        
        <!-- Formulaire d'ajout -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Ajouter un Service</h2>
            <form action="process_services.php" method="POST" class="space-y-4">
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700">Nom du Service</label>
                    <input type="text" id="nom" name="nom" required 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="icone" class="block text-sm font-medium text-gray-700">Code SVG de l'icône</label>
                    <textarea id="icone" name="icone" rows="3" required
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p class="mt-1 text-sm text-gray-500">Coller le code SVG de l'icône (ex: &lt;path.../&gt;)</p>
                </div>
                <div>
                    <button type="submit" name="ajouter" 
                            class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Ajouter le Service
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Liste des services -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Services Disponibles</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icône</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        try {
                            // Correction: utiliser 'services' au lieu de 'servicesacc' si c'est le nom correct de votre table
                            $query = $pdo->query("SELECT * FROM servicesacc ORDER BY nom");
                            while ($service = $query->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($service['nom']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                                    <?= $service['icone'] ?>
                                </svg>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $service['est_actif'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $service['est_actif'] ? 'Actif' : 'Masqué' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <?php if ($service['est_actif']): ?>
                                    <a href="process_services.php?masquer=<?= $service['id'] ?>" 
                                       class="text-yellow-600 hover:text-yellow-900">
                                        Masquer
                                    </a>
                                <?php else: ?>
                                    <a href="process_services.php?afficher=<?= $service['id'] ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        Afficher
                                    </a>
                                <?php endif; ?>
                                <a href="process_services.php?supprimer=<?= $service['id'] ?>" 
                                   class="text-red-600 hover:text-red-900" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce service?')">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='4' class='px-6 py-4 text-center text-red-600'>Erreur lors du chargement des services: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>