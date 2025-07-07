<?php
require_once '../includes/db_connect.php';
require_once '../controllers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer le service_id depuis l'URL
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;

try {
    // Requête de base
    $sql = "
        SELECT 
            sacc.nom AS service_nom,
            p.id AS prestataire_id,
            p.ville,
            p.commune,
            p.photo_profil,
            p.type_prestataire,
            u.nom,
            u.prenom,
            serv.tarif,
            serv.devise,
            AVG(e.note) AS note_moyenne,
            COUNT(e.id) AS nombre_avis
        FROM servicesacc sacc
        JOIN services serv ON sacc.nom = serv.nom
        JOIN prestataires p ON serv.prestataire_id = p.id
        JOIN users u ON p.user_id = u.id
        LEFT JOIN evaluations e ON p.id = e.prestataire_id
        WHERE p.etat_compte = 'Validé'
    ";
    
    // Ajouter le filtre si un service_id est spécifié
    if ($service_id) {
        $sql .= " AND sacc.id = :service_id";
    }
    
    $sql .= " GROUP BY sacc.nom, p.id, u.nom, u.prenom, serv.tarif, serv.devise
              ORDER BY sacc.nom, note_moyenne DESC";
    
    // Préparation de la requête
    $stmt = $pdo->prepare($sql);
    
    // Liaison du paramètre si nécessaire
    if ($service_id) {
        $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
    }
    
    // Exécution
    $stmt->execute();
    
    $servicesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser les données par service
    $services = [];
    foreach ($servicesData as $row) {
        $serviceName = $row['service_nom'];
        if (!isset($services[$serviceName])) {
            $services[$serviceName] = [];
        }
        $services[$serviceName][] = $row;
    }
    
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tous nos prestataires</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<meta name="csrf-token" content="<?= isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) : '' ?>">
  <style>
    .star-icon {
      display: inline-block;
      width: 1em;
      height: 1em;
      fill: currentColor;
    }
    .service-section {
      scroll-margin-top: 7rem;
    }
    .prestataire-card {
      transition: all 0.3s ease;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    .prestataire-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .profile-image {
      transition: transform 0.3s ease;
    }
    .prestataire-card:hover .profile-image {
      transform: scale(1.05);
    }
  </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen">

  <!-- Header -->
   <?php require "portions/header.php" ?>

  <!-- Contenu principal -->
  <main class="pt-28 pb-16 max-w-7xl mx-auto px-4">

    <?php if (empty($services)): ?>
      <div class="bg-white p-8 rounded-xl shadow-md text-center max-w-2xl mx-auto">
        <div class="text-blue-500 mb-4">
          <i class="fas fa-users-slash text-5xl"></i>
        </div>
        <p class="text-gray-500 text-lg font-medium">Aucun prestataire disponible pour le moment.</p>
        <p class="text-gray-400 mt-2">Nos équipes travaillent à recruter les meilleurs professionnels.</p>
      </div>
    <?php else: ?>
      <!-- Navigation rapide -->
      <div class="mb-8 bg-white p-6 rounded-xl shadow-md sticky top-20 z-10">
        <h2 class="font-semibold text-gray-700 mb-3 text-lg">Prestataires :</h2>
        <div class="flex flex-wrap gap-3">
          <?php foreach (array_keys($services) as $serviceName): ?>
            <a href="#<?= urlencode(strtolower($serviceName)) ?>" 
               class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-sm font-medium transition flex items-center">
              <i class="fas fa-chevron-right text-xs mr-2"></i>
              <?= htmlspecialchars($serviceName) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Liste des services et prestataires -->
      <div class="space-y-16">
        <?php foreach ($services as $serviceName => $prestataires): ?>
          <section id="<?= urlencode(strtolower($serviceName)) ?>" class="service-section">
            <div class="flex items-center justify-between mb-8">
              <div>
                <h2 class="text-2xl font-bold text-gray-800">
                  <?= htmlspecialchars($serviceName) ?>
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                  <?= count($prestataires) ?> prestataire<?= count($prestataires) > 1 ? 's' : '' ?> disponible<?= count($prestataires) > 1 ? 's' : '' ?>
                </p>
              </div>
              <a href="prestataires.php?service_nom=<?= urlencode($serviceName) ?>" 
                 class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                Voir tous <i class="fas fa-arrow-right ml-1 text-xs"></i>
              </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($prestataires as $prestataire): 
                // Définir les valeurs par défaut AVANT utilisation
                $note = $prestataire['note_moyenne'] ?? 0;
                $nombre_avis = $prestataire['nombre_avis'] ?? 0; // Initialisation explicite
                
                // Gestion de la photo de profil
                $photo_profil = 'https://cdn-icons-png.flaticon.com/512/219/219969.png'; // Image par défaut
                if (!empty($prestataire['photo_profil'])) {
                    if (strpos($prestataire['photo_profil'], 'http') === 0) {
                        $photo_profil = $prestataire['photo_profil']; // URL externe
                    } else {
                        $photo_profil = '../uploads/' . $prestataire['photo_profil']; // Chemin local
                    }
                }
            ?>
                <div class="prestataire-card bg-white rounded-2xl shadow-md overflow-hidden flex" style="max-width: 500px;">
                <!-- Partie gauche avec l'image de profil -->
                <div class="w-1/3 bg-gradient-to-b from-blue-50 to-blue-100 flex flex-col items-center justify-center p-4 relative">
                    <img src="<?= $photo_profil ?>"
                        alt="<?= htmlspecialchars($prestataire['prenom'] . ' ' . $prestataire['nom']) ?>"
                        class="profile-image w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                    
                    <?php if ($prestataire['type_prestataire'] === 'entreprise'): ?>
                    <span class="mt-3 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                        <i class="fas fa-building mr-1 text-xs"></i> Entreprise
                    </span>
                    <?php else: ?>
                    <span class="mt-3 bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                        <i class="fas fa-user-tie mr-1 text-xs"></i> Indépendant
                    </span>
                    <?php endif; ?>
                </div>
  
            <!-- Partie droite avec les détails -->
            <div class="w-2/3 p-5">
                <h3 class="font-bold text-gray-800 text-lg mb-1 font-sans"><?= htmlspecialchars($prestataire['prenom'] . ' ' . $prestataire['nom']) ?></h3>
                
                <div class="flex items-center mb-3">
                <div class="flex mr-2">
                    <?php 
                    if ($nombre_avis > 0) {
                        $note_entiere = floor($note);
                        $has_half_star = ($note - $note_entiere) >= 0.5;
                        $empty_stars = 5 - $note_entiere - ($has_half_star ? 1 : 0);
                        
                        for ($i = 0; $i < $note_entiere; $i++): ?>
                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                        <?php endfor; 
                        
                        if ($has_half_star): ?>
                            <i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>
                        <?php endif;
                        
                        for ($i = 0; $i < $empty_stars; $i++): ?>
                            <i class="far fa-star text-gray-300 text-sm"></i>
                        <?php endfor;
                    } else {
                        for ($i = 0; $i < 5; $i++): ?>
                            <i class="far fa-star text-gray-300 text-sm"></i>
                        <?php endfor;
                    }
                    ?>
                </div>
                <span class="text-xs text-gray-600 font-medium">
                    <?= $nombre_avis > 0 ? number_format($note, 1).' ('.$nombre_avis.' avis)' : 'Aucun avis' ?>
                </span>
                </div>

                <div class="flex items-center text-sm text-gray-600 mb-4 font-medium">
                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                <span><?= htmlspecialchars($prestataire['ville']) ?>, <?= htmlspecialchars($prestataire['commune']) ?></span>
                </div>

                <!-- <div class="flex justify-between items-center bg-blue-50 rounded-xl p-3 mb-4">
                <span class="text-sm font-medium text-gray-600">Tarif</span>
                <span class="text-green-600 font-bold">
                   <?= number_format($prestataire['tarif'] ?? 0, 2) ?> <?= $prestataire['devise'] === 'USD' ? '$' : 'CDF' ?>
                </span>
                </div> -->

                <div class="flex space-x-3">
                <a href="profilPresta.php?id=<?= $prestataire['prestataire_id'] ?>" 
                    class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl font-medium transition flex items-center justify-center text-sm">
                    <i class="fas fa-eye mr-2 text-xs"></i> Voir profil
                </a>
                <button class="favorite-btn w-10 h-10 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-xl transition" data-prestataire-id="<?= $prestataire['prestataire_id'] ?>">
                    <i class="far fa-heart text-gray-500"></i>
                </button>
                </div>
            </div>
            </div>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- Footer -->
  <?php
    require "portions/footer.php" ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dictionnaire pour suivre l'état de traitement de chaque bouton
    const processingStates = {};
    
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.favorite-btn');
        if (!btn) return;
        
        const prestataireId = btn.dataset.prestataireId;
        const heartIcon = btn.querySelector('i');
        
        // Vérifier si déjà en traitement
        if (processingStates[prestataireId]) return;
        processingStates[prestataireId] = true;
        
        // Récupérer le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!csrfToken) {
            console.error('CSRF token manquant');
            alert('Erreur de sécurité. Veuillez rafraîchir la page.');
            processingStates[prestataireId] = false;
            return;
        }

        // Désactiver le bouton pendant le traitement
        btn.disabled = true;
        heartIcon.classList.add('opacity-50');

        try {
            const response = await fetch('../controllers/favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ 
                    prestataire_id: prestataireId 
                })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erreur serveur');
            }
            
            const data = await response.json();
            
            // Mise à jour de l'interface
            if (data.action === 'added') {
                heartIcon.classList.replace('far', 'fas');
                heartIcon.classList.add('text-red-500');
                btn.setAttribute('aria-label', 'Retirer des favoris');
            } else {
                heartIcon.classList.replace('fas', 'far');
                heartIcon.classList.remove('text-red-500');
                btn.setAttribute('aria-label', 'Ajouter aux favoris');
            }
            
            // Animation de feedback
            heartIcon.classList.add('animate-ping');
            setTimeout(() => {
                heartIcon.classList.remove('animate-ping');
            }, 500);
            
            // Mettre à jour le compteur de favoris si présent dans le DOM
            const favoritesCounter = document.getElementById('favorites-counter');
            if (favoritesCounter) {
                favoritesCounter.textContent = data.total_favorites;
            }
            
        } catch (error) {
            console.error('Erreur:', error);
            
            // Afficher un toast d'erreur (si vous avez un système de notifications)
            if (typeof showToast === 'function') {
                showToast({
                    type: 'error',
                    message: error.message || 'Une erreur est survenue'
                });
            } else {
                // Fallback simple
                alert(error.message || 'Une erreur est survenue');
            }
            
            // Revert visual changes if error
            heartIcon.classList.toggle('far');
            heartIcon.classList.toggle('fas');
            heartIcon.classList.toggle('text-red-500');
            
        } finally {
            // Réactiver le bouton
            btn.disabled = false;
            heartIcon.classList.remove('opacity-50');
            processingStates[prestataireId] = false;
        }
    });

    // Précharger les états des favoris au chargement de la page
    async function loadInitialFavoritesStates() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) return;
        
        try {
            const response = await fetch('../controllers/favorites.php?action=get_favorites', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            if (response.ok) {
                const favorites = await response.json();
                
                document.querySelectorAll('.favorite-btn').forEach(btn => {
                    const prestataireId = btn.dataset.prestataireId;
                    const heartIcon = btn.querySelector('i');
                    
                    if (favorites.includes(parseInt(prestataireId))) {
                        heartIcon.classList.replace('far', 'fas');
                        heartIcon.classList.add('text-red-500');
                        btn.setAttribute('aria-label', 'Retirer des favoris');
                    }
                });
            }
        } catch (error) {
            console.error('Erreur chargement favoris:', error);
        }
    }
    
    // Appeler la fonction de chargement initial
    loadInitialFavoritesStates();
});
</script>
</body>
</html>