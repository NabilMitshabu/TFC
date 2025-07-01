<?php
include '../includes/db_connect.php';

function getPendingProviders($pdo) {
    $stmt = $pdo->query("SELECT p.*, u.nom, u.email 
                        FROM prestataires p
                        JOIN users u ON p.user_id = u.id
                        WHERE p.etat_compte = 'En attente'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserServices($pdo, $prestataireId) {
    $stmt = $pdo->prepare("SELECT s.nom FROM services s 
                          WHERE s.prestataire_id = ?");
    $stmt->execute([$prestataireId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pendingProviders = getPendingProviders($pdo);
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-blue-800">Inscriptions en attente</h2>
    <p class="text-gray-600">Gestion des nouveaux prestataires à valider</p>
</div>

<div class="bg-white p-6 rounded-lg shadow mb-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-blue-800">Liste des prestataires en attente</h3>
        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" id="export-pending">
            <i class="fas fa-file-export mr-2"></i>Exporter
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom / Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coordonnées</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Services</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documents</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="pending-registrations-table">
                <?php foreach ($pendingProviders as $provider): 
                    $services = getUserServices($pdo, $provider['id']);
                ?>
                <tr data-id="<?= htmlspecialchars($provider['id']) ?>">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium"><?= htmlspecialchars($provider['nom'] ?? '') ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($provider['type_prestataire'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div><?= htmlspecialchars($provider['email'] ?? '') ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($provider['telephone'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            <?php if (!empty($services)): ?>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($services as $service): ?>
                                        <li><?= htmlspecialchars($service['nom'] ?? '') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-gray-400">Aucun service enregistré</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex space-x-2">
                            <?php if (!empty($provider['photo_profil'])): ?>
                            <a href="../uploads/<?= htmlspecialchars($provider['photo_profil']) ?>" class="text-blue-600 hover:underline" target="_blank">
                                <i class="fas fa-portrait mr-1"></i>Photo
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($provider['carte_identite'])): ?>
                            <a href="../uploads/<?= htmlspecialchars($provider['carte_identite']) ?>" class="text-blue-600 hover:underline" target="_blank">
                                <i class="fas fa-id-card mr-1"></i>ID
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                        <button class="approve-btn bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700" 
                                data-id="<?= htmlspecialchars($provider['id']) ?>">
                            <i class="fas fa-check mr-1"></i>Valider
                        </button>
                        <button class="reject-btn bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700" 
                                data-id="<?= htmlspecialchars($provider['id']) ?>">
                            <i class="fas fa-times mr-1"></i>Refuser
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de refus (inchangé) -->
<div id="reject-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 class="text-xl font-bold text-blue-800 mb-4">Refuser l'inscription</h3>
        <p class="mb-2" id="reject-user-info"></p>
        
        <div class="mb-4">
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Motif du refus</label>
            <textarea id="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button id="cancel-reject" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                Annuler
            </button>
            <button id="confirm-reject" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Confirmer
            </button>
        </div>
    </div>
</div>

<script>
// Gestion des actions via AJAX (version simplifiée)
document.addEventListener('DOMContentLoaded', function() {
    // Export CSV
    document.getElementById('export-pending').addEventListener('click', function() {
        window.location.href = 'export_pending.php';
    });

    // Validation d'inscription
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            updateProviderStatus(id, 'Validé');
        });
    });

    // Refus d'inscription
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const name = row.querySelector('.font-medium').textContent;
            const type = row.querySelector('.text-gray-500').textContent;
            
            document.getElementById('reject-user-info').textContent = `${name} (${type})`;
            document.getElementById('reject-modal').classList.remove('hidden');
            document.getElementById('confirm-reject').setAttribute('data-id', id);
        });
    });

    // Annulation du refus
    document.getElementById('cancel-reject').addEventListener('click', function() {
        document.getElementById('reject-modal').classList.add('hidden');
    });

    // Confirmation du refus
    document.getElementById('confirm-reject').addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const reason = document.getElementById('reason').value;
        updateProviderStatus(id, 'Rejeté', reason);
    });
});

// Fonction principale inchangée
function updateProviderStatus(id, status, reason = '') {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);
    if (reason) formData.append('reason', reason);

    axios.post('process_provider.php', formData)
        .then(response => {
            if (response.data.success) {
                // Suppression silencieuse de la ligne
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.5s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 500);
                }
            }
           
        })
        .catch(error => {
            console.error('Erreur:', error); // Seulement dans la console
        });
}
</script>