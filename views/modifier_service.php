<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user']['prestataire_id'])) {
    header("Location: signInClient.php");
    exit;
}

$prestataire_id = $_SESSION['user']['prestataire_id'];
$service_id = $_GET['id'] ?? null;

if (!$service_id || !is_numeric($service_id)) {
    die("ID de service invalide.");
}

// Récupération du service existant
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND prestataire_id = ?");
$stmt->execute([$service_id, $prestataire_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service introuvable ou non autorisé.");
}

// Récupération des services standards
$stmt = $pdo->query("SELECT id, nom FROM servicesacc WHERE est_actif = 1 ORDER BY nom");
$servicesacc = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Service</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function toggleCustomInput() {
            const select = document.getElementById('nom_standard');
            const customDiv = document.getElementById('custom_nom_div');
            customDiv.style.display = (select.value === 'autre') ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            toggleCustomInput();
        });
    </script>
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Modifier le Service</h1>

        <form method="POST" action="../controllers/service_save.php" class="space-y-4">
            <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['id']) ?>">

            <!-- Liste déroulante des services standards -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Nom du service</label>
                <select name="nom_standard" id="nom_standard" onchange="toggleCustomInput()" class="block w-full border rounded p-2">
                    <option value="">-- Sélectionner un service --</option>
                    <?php foreach ($servicesacc as $sacc): ?>
                        <option value="<?= htmlspecialchars($sacc['nom']) ?>"
                            <?= $service['nom'] === $sacc['nom'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sacc['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="autre" <?= !in_array($service['nom'], array_column($servicesacc, 'nom')) ? 'selected' : '' ?>>Autre service</option>
                </select>
            </div>

            <!-- Champ pour un service personnalisé -->
            <div id="custom_nom_div" style="display: none;">
                <label class="block text-sm font-medium text-gray-700">Nom personnalisé</label>
                <input type="text" name="nom_custom" value="<?= !in_array($service['nom'], array_column($servicesacc, 'nom')) ? htmlspecialchars($service['nom']) : '' ?>" class="block w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" required class="mt-1 block w-full border rounded p-2" rows="4"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tarif</label>
                <input type="number" step="0.01" name="tarif" value="<?= htmlspecialchars($service['tarif'] ?? '') ?>" class="mt-1 block w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Devise</label>
                <select name="devise" class="mt-1 block w-full border rounded p-2">
                    <option value="CDF" <?= $service['devise'] === 'CDF' ? 'selected' : '' ?>>CDF</option>
                    <option value="USD" <?= $service['devise'] === 'USD' ? 'selected' : '' ?>>USD</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</body>
</html>
