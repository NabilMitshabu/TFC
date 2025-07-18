<?php

require_once '../includes/db_connect.php';

// Vérifier si l'ID du prestataire est passé en paramètre
$prestataire_id = $_GET['id'] ?? null;

if (!$prestataire_id) {
    header("Location: /index.php");
    exit();
}

// Fonction pour générer le chemin de la photo de profil
function getProfilePhotoPath($photo_profil) {
    if (empty($photo_profil)) {
        return 'https://cdn-icons-png.flaticon.com/512/219/219969.png'; // Image par défaut
    }
    if (filter_var($photo_profil, FILTER_VALIDATE_URL)) {
        return $photo_profil; // URL externe
    } else {
        return '../uploads/' . $photo_profil; // Chemin local
    }
}

try {
    // Récupérer les informations du prestataire
    $stmt = $pdo->prepare("
        SELECT p.*, u.nom, u.prenom, u.email
        FROM prestataires p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$prestataire_id]);
    $prestataire = $stmt->fetch();

    if (!$prestataire) {
        throw new Exception("Prestataire non trouvé");
    }

    // Récupérer le chemin de la photo de profil
    $photo_profil = getProfilePhotoPath($prestataire['photo_profil']);

    // Récupérer les services du prestataire
    $stmt = $pdo->prepare("
        SELECT * FROM services
        WHERE prestataire_id = ?
        ORDER BY nom
    ");
    $stmt->execute([$prestataire_id]);
    $services = $stmt->fetchAll();

    // Calculer la note moyenne
    $stmt = $pdo->prepare("
        SELECT AVG(note) as moyenne, COUNT(*) as total
        FROM evaluations
        WHERE prestataire_id = ?
    ");
    $stmt->execute([$prestataire_id]);
    $evaluations = $stmt->fetch();

    $note_moyenne = $evaluations['moyenne'] ?? 0;
    $total_avis = $evaluations['total'] ?? 0;

    // Récupérer les évaluations récentes
    $stmt = $pdo->prepare("
        SELECT e.*, u.prenom, u.nom
        FROM evaluations e
        JOIN users u ON e.user_id = u.id
        WHERE e.prestataire_id = ?
        ORDER BY e.date_evaluation DESC
        LIMIT 3
    ");
    $stmt->execute([$prestataire_id]);
    $dernieres_evaluations = $stmt->fetchAll();

    // Récupérer les images du prestataire
    $stmt = $pdo->prepare("SELECT * FROM prestataire_images WHERE prestataire_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$prestataire_id]);
    $images = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

// Fonction pour générer les étoiles
function genererEtoiles($note) {
    $etoiles_pleines = floor($note);
    $has_half_star = ($note - $etoiles_pleines) >= 0.5;
    $empty_stars = 5 - $etoiles_pleines - ($has_half_star ? 1 : 0);

    $html = '';
    for ($i = 0; $i < $etoiles_pleines; $i++) {
        $html .= '<i class="fas fa-star text-yellow-400"></i>';
    }
    if ($has_half_star) {
        $html .= '<i class="fas fa-star-half-alt text-yellow-400"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star text-gray-300"></i>';
    }
    return $html;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil de <?= htmlspecialchars($prestataire['prenom'] . ' ' . htmlspecialchars($prestataire['nom'])) ?> | ClicService</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
        .avis-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out;
        }
        .avis-container.visible {
            max-height: 1000px; /* Ajustez cette valeur si nécessaire */
        }
        .toggle-avis-btn {
            transition: all 0.3s ease;
        }
        .toggle-avis-btn:hover {
            transform: translateY(-2px);
        }
        .toggle-avis-btn i {
            transition: transform 0.3s ease;
        }
        .toggle-avis-btn.active i {
            transform: rotate(180deg);
        }
        .gallery-grid {
            perspective: 1000px;
        }
        .gallery-item {
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen">

<!-- Header -->
<?php require "portions/header.php"; ?>

<main class="max-w-5xl mx-auto mt-32 mb-20 px-4">
    <!-- Profile Card -->
    <div class="prestataire-card bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="flex flex-col md:flex-row">
            <!-- Partie gauche avec photo et info basique -->
            <div class="w-full md:w-1/3 bg-gradient-to-b from-blue-50 to-blue-100 p-8 flex flex-col items-center">
                <img src="<?= $photo_profil ?>"
                     alt="<?= htmlspecialchars($prestataire['prenom'] . ' ' . htmlspecialchars($prestataire['nom'])) ?>"
                     class="profile-image w-40 h-40 rounded-full object-cover border-4 border-white shadow-lg mb-6">
                <h2 class="text-2xl font-bold text-center text-gray-800">
                    <?= htmlspecialchars($prestataire['prenom'] . ' ' . htmlspecialchars($prestataire['nom'])) ?>
                </h2>
                <?php if ($prestataire['type_prestataire'] === 'entreprise'): ?>
                    <span class="mt-3 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                        <i class="fas fa-building mr-1 text-xs"></i> Entreprise
                    </span>
                <?php else: ?>
                    <span class="mt-3 bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                        <i class="fas fa-user-tie mr-1 text-xs"></i> Indépendant
                    </span>
                <?php endif; ?>
                <div class="mt-6 flex items-center text-sm text-gray-600 font-medium">
                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                    <span><?= htmlspecialchars($prestataire['ville'] ?? 'Non spécifié') ?>, <?= htmlspecialchars($prestataire['commune'] ?? '') ?></span>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-600 font-medium">
                    <i class="fas fa-phone-alt mr-2 text-blue-500"></i>
                    <span><?= htmlspecialchars($prestataire['telephone'] ?? 'Non spécifié') ?></span>
                </div>
                <div class="mt-6 text-center">
                    <div class="flex justify-center items-center mb-1">
                        <?= genererEtoiles($note_moyenne) ?>
                    </div>
                    <span class="text-sm text-gray-600 font-medium">
                        <?= $total_avis > 0 ? number_format($note_moyenne, 1) . '/5 (' . $total_avis . ' avis)' : 'Aucun avis' ?>
                    </span>
                </div>
            </div>
            
            <!-- Partie droite avec contenu détaillé -->
            <div class="w-full md:w-2/3 p-8">
                <!-- À propos -->
                <section class="mb-8">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i> À propos
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 shadow-lg">
                        <p class="text-gray-700 leading-relaxed text-justify">
                            <?= !empty($prestataire['description']) ? nl2br(htmlspecialchars($prestataire['description'])) : 'Ce prestataire n\'a pas encore ajouté de description.' ?>
                        </p>
                    </div>
                </section>
                
                <!-- Services et Tarifs -->
                <section class="mb-8">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-list-alt text-blue-500 mr-2"></i> Services proposés
                    </h3>
                    <?php if (!empty($services)): ?>
                        <div class="space-y-4">
                            <?php foreach ($services as $service): ?>
                                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-gray-800"><?= htmlspecialchars($service['nom']) ?></h4>
                                            <?php if (!empty($service['description'])): ?>
                                                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($service['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg text-sm font-medium">
                                            <?= number_format($service['tarif'], 2) ?> <?= $service['devise'] === 'USD' ? '$' : 'CDF' ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($service['disponibilite'])): ?>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <i class="far fa-clock mr-2"></i>
                                            <span><?= htmlspecialchars($service['disponibilite']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 rounded-xl p-4 text-center text-gray-500">
                            Aucun service disponible pour le moment
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Avis clients -->
                <section class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-star text-blue-500 mr-2"></i> Avis clients
                        </h3>
                        <?php if (!empty($dernieres_evaluations)): ?>
                            <button id="toggleAvisBtn" class="toggle-avis-btn bg-blue-50 text-blue-600 px-4 py-2 rounded-lg flex items-center text-sm font-medium">
                                Afficher les avis <i class="fas fa-chevron-down ml-2 text-xs"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div id="avisContainer" class="avis-container">
                        <?php if (!empty($dernieres_evaluations)): ?>
                            <div class="space-y-4">
                                <?php foreach ($dernieres_evaluations as $evaluation): ?>
                                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-medium text-gray-800">
                                                <?= htmlspecialchars($evaluation['prenom'] . ' ' . htmlspecialchars($evaluation['nom'])) ?>
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <?= date('d/m/Y', strtotime($evaluation['date_evaluation'])) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center mb-2">
                                            <?= genererEtoiles($evaluation['note']) ?>
                                            <span class="ml-2 text-sm font-medium text-gray-600">
                                                <?= $evaluation['note'] ?>/5
                                            </span>
                                        </div>
                                        <?php if (!empty($evaluation['commentaire'])): ?>
                                            <p class="text-gray-700 text-sm mt-2">
                                                <?= nl2br(htmlspecialchars($evaluation['commentaire'])) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-gray-50 rounded-xl p-4 text-center text-gray-500">
                                Aucun avis pour le moment
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Bouton de contact -->
                <div class="text-center mt-8">
                    <a href="/views/demandeService.php?prestataire_id=<?= $prestataire_id ?>" 
                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all font-medium">
                        <i class="fas fa-paper-plane mr-2"></i> Demander un service
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Espacement entre le bloc précédent et la galerie -->
    <div class="my-12"></div>
    
    <!-- Galerie d'images -->
    <section class="mb-12">
        <h3 class="text-xl font-semibold mb-6 text-gray-800 flex items-center">
            <i class="fas fa-images text-blue-500 mr-2"></i> Galerie de réalisations
        </h3>
        
        <?php if (!empty($images)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 gallery-grid">
                <?php foreach ($images as $index => $image): ?>
                    <div class="gallery-item relative rounded-xl overflow-hidden shadow-lg transform transition-all duration-500 hover:scale-105 hover:shadow-2xl"
                         onclick="openLightbox('<?= htmlspecialchars($image['image_path']) ?>')">
                        <img src="../uploads/prestataires/<?= htmlspecialchars($image['image_path']) ?>" 
                             alt="Réalisation <?= $index + 1 ?> de <?= htmlspecialchars($prestataire['prenom']) ?>"
                             class="w-full h-48 object-cover transition-opacity duration-300 hover:opacity-90"
                             loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 text-center">
                <div class="max-w-md mx-auto">
                    <div class="text-blue-400 mb-4">
                        <i class="fas fa-camera-retro text-4xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-700 mb-2">Galerie vide</h4>
                    <p class="text-gray-500 mb-4">Ce prestataire n'a pas encore partagé ses réalisations</p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleAvisBtn');
        const avisContainer = document.getElementById('avisContainer');
        
        if (toggleBtn && avisContainer) {
            toggleBtn.addEventListener('click', function() {
                avisContainer.classList.toggle('visible');
                this.classList.toggle('active');
                
                if (avisContainer.classList.contains('visible')) {
                    this.innerHTML = 'Masquer les avis <i class="fas fa-chevron-up ml-2 text-xs"></i>';
                } else {
                    this.innerHTML = 'Afficher les avis <i class="fas fa-chevron-down ml-2 text-xs"></i>';
                }
            });
        }
    });

    // Lightbox functionality
    function openLightbox(imagePath) {
        const lightbox = document.createElement('div');
        lightbox.className = 'fixed inset-0 bg-black/95 z-50 flex items-center justify-center';
        lightbox.innerHTML = `
            <div class="relative max-w-6xl w-full px-4">
                <button onclick="this.parentElement.parentElement.remove()" class="absolute -top-16 right-0 text-white hover:text-blue-300 transition-colors">
                    <i class="fas fa-times text-3xl"></i>
                </button>
                <img src="../uploads/prestataires/${imagePath}" alt="" class="lightbox-image rounded-lg shadow-xl mx-auto max-h-[80vh]">
            </div>
        `;
        document.body.appendChild(lightbox);
    }
</script>

</body>
</html>