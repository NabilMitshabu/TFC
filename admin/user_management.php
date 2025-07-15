<?php
include '../includes/db_connect.php';

// Récupérer tous les utilisateurs clients et prestataires validés
function getClients($pdo) {
    $stmt = $pdo->query("
        SELECT 
            u.id, 
            u.nom, 
            u.prenom, 
            c.ville, 
            c.commune, 
            c.telephone, 
            p.etat_compte,
            CASE 
                WHEN p.user_id IS NOT NULL THEN 'Prestataire'
                WHEN c.user_id IS NOT NULL THEN 'Client'
                ELSE 'Inconnu'
            END AS type_utilisateur
        FROM 
            users u 
        LEFT JOIN 
            client c ON u.id = c.user_id
        LEFT JOIN 
            prestataires p ON u.id = p.user_id
        WHERE 
            p.etat_compte = 'Validé' OR p.user_id IS NULL
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Bannir un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ban_user_id'])) {
    $userId = $_POST['ban_user_id'];
    $reason = $_POST['ban_reason'];

    // Mettre à jour l'état du compte dans la table prestataires
    $stmt = $pdo->prepare("UPDATE prestataires SET etat_compte = 'Rejeté' WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Enregistrer la raison dans une table de logs (à créer)
    $stmt = $pdo->prepare("INSERT INTO ban_logs (user_id, reason) VALUES (?, ?)");
    $stmt->execute([$userId, $reason]);

    header('Location: user_management.php');
    exit();
}

$clients = getClients($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Gestion des Utilisateurs</h2>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border">Nom</th>
                    <th class="py-2 px-4 border">Prénom</th>
                    <th class="py-2 px-4 border">Type</th>
                    <th class="py-2 px-4 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td class="border py-2 px-4"><?= htmlspecialchars($client['nom'] ?? '') ?></td>
                        <td class="border py-2 px-4"><?= htmlspecialchars($client['prenom'] ?? '') ?></td>
                        <td class="border py-2 px-4"><?= htmlspecialchars($client['type_utilisateur'] ?? '') ?></td>
                        <td class="border py-2 px-4">
                            <button onclick="document.getElementById('ban-modal-<?= $client['id'] ?>').style.display='block'" class="bg-red-500 text-white py-1 px-2 rounded">Bannir</button>
                        </td>
                    </tr>

                    <!-- Modal pour bannir l'utilisateur -->
                    <div id="ban-modal-<?= $client['id'] ?>" class="modal hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white p-6 rounded shadow">
                            <h3 class="text-lg font-bold mb-2">Bannir l'utilisateur</h3>
                            <form method="POST">
                                <input type="hidden" name="ban_user_id" value="<?= $client['id'] ?>">
                                <textarea name="ban_reason" placeholder="Raison du bannissement" required class="border w-full p-2 mb-4"></textarea>
                                <button type="submit" class="bg-red-500 text-white py-1 px-4 rounded">Confirmer</button>
                                <button type="button" onclick="document.getElementById('ban-modal-<?= $client['id'] ?>').style.display='none'" class="bg-gray-300 text-gray-700 py-1 px-4 rounded">Annuler</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Fermer la modal quand on clique en dehors
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
