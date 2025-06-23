<?php
require_once '../includes/db_connect.php';

try {
    // Récupérer tous les services disponibles avec leurs prestataires
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
        GROUP BY sacc.nom, p.id, u.nom, u.prenom, serv.tarif, serv.devise
        ORDER BY sacc.nom, note_moyenne DESC
    ";
    
    $servicesQuery = $pdo->query($sql);
    
    if ($servicesQuery === false) {
        throw new Exception("Erreur SQL: " . print_r($pdo->errorInfo(), true));
    }
    
    $servicesData = $servicesQuery->fetchAll(PDO::FETCH_ASSOC);
    
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
  </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold text-blue-600">ClicService</h1>
      <nav class="space-x-6 text-sm font-medium text-gray-600">
        <a href="index.php" class="hover:text-blue-500">Accueil</a>
        <a href="#" class="hover:text-blue-500">Connexion</a>
        <a href="views/inscription.php" class="hover:text-blue-500">Inscription</a>
      </nav>
    </div>
  </header>

  <!-- Contenu principal -->
  <main class="pt-28 pb-16 max-w-7xl mx-auto px-4">
    <!-- Titre principal -->
    <div class="text-center mb-12">
      <h1 class="text-4xl font-bold text-blue-600 mb-2">Nos prestataires de services</h1>
      <p class="text-gray-600">Trouvez le professionnel qu'il vous faut parmi notre sélection</p>
    </div>

    <?php if (empty($services)): ?>
      <div class="bg-white p-8 rounded-xl shadow-md text-center">
        <p class="text-gray-500 text-lg">Aucun prestataire disponible pour le moment.</p>
        <p class="text-gray-400 mt-2">Nos équipes travaillent à recruter les meilleurs professionnels.</p>
      </div>
    <?php else: ?>
      <!-- Navigation rapide -->
      <div class="mb-8 bg-white p-4 rounded-xl shadow-md sticky top-20 z-10">
        <h2 class="font-semibold text-gray-700 mb-2">Services disponibles :</h2>
        <div class="flex flex-wrap gap-2">
          <?php foreach (array_keys($services) as $serviceName): ?>
            <a href="#<?= urlencode(strtolower($serviceName)) ?>" 
               class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 text-sm transition">
              <?= htmlspecialchars($serviceName) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Liste des services et prestataires -->
      <div class="space-y-16">
        <?php foreach ($services as $serviceName => $prestataires): ?>
          <section id="<?= urlencode(strtolower($serviceName)) ?>" class="service-section">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-bold text-gray-800">
                <?= htmlspecialchars($serviceName) ?>
                <span class="text-sm font-normal text-gray-500 ml-2">
                  (<?= count($prestataires) ?> prestataire<?= count($prestataires) > 1 ? 's' : '' ?>)
                </span>
              </h2>
              <a href="prestataires.php?service_nom=<?= urlencode($serviceName) ?>" 
                 class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Voir tous →
              </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <?php foreach ($prestataires as $prestataire): 
                $note = $prestataire['note_moyenne'] ?? 0;
                $nombre_avis = $prestataire['nombre_avis'] ?? 0;
              ?>
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                  <div class="flex items-center space-x-4 mb-4">
                    <img src="<?= htmlspecialchars($prestataire['photo_profil'] ?? 'https://cdn-icons-png.flaticon.com/512/219/219969.png') ?>"
                         alt="<?= htmlspecialchars($prestataire['prenom'] . ' ' . $prestataire['nom']) ?>"
                         class="w-16 h-16 rounded-full object-cover border-2 border-blue-300">
                    <div>
                      <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($prestataire['prenom'] . ' ' . $prestataire['nom']) ?></h3>
                      <p class="text-sm text-gray-500">
                        <?= htmlspecialchars($prestataire['type_prestataire'] === 'entreprise' ? 'Entreprise' : 'Indépendant') ?>
                      </p>
                    </div>
                  </div>

                  <div class="flex items-center mb-2">
                    <div class="flex mr-2">
                      <?php 
                      if ($nombre_avis > 0) {
                          $note_entiere = floor($note);
                          $has_half_star = ($note - $note_entiere) >= 0.5;
                          $empty_stars = 5 - $note_entiere - ($has_half_star ? 1 : 0);
                          
                          for ($i = 0; $i < $note_entiere; $i++): ?>
                              <svg class="w-4 h-4 text-yellow-400 star-icon" viewBox="0 0 20 20">
                                  <polygon points="10,1 12.59,7.36 19.51,7.36 13.97,11.63 16.56,17.99 10,13.72 3.44,17.99 6.03,11.63 0.49,7.36 7.41,7.36"/>
                              </svg>
                          <?php endfor; 
                          
                          if ($has_half_star): ?>
                              <svg class="w-4 h-4 text-yellow-400 star-icon" viewBox="0 0 20 20">
                                  <defs>
                                      <linearGradient id="half-star">
                                          <stop offset="50%" stop-color="currentColor"/>
                                          <stop offset="50%" stop-color="#d1d5db"/>
                                      </linearGradient>
                                  </defs>
                                  <polygon fill="url(#half-star)" points="10,1 12.59,7.36 19.51,7.36 13.97,11.63 16.56,17.99 10,13.72 3.44,17.99 6.03,11.63 0.49,7.36 7.41,7.36"/>
                              </svg>
                          <?php endif;
                          
                          for ($i = 0; $i < $empty_stars; $i++): ?>
                              <svg class="w-4 h-4 text-gray-300 star-icon" viewBox="0 0 20 20">
                                  <polygon points="10,1 12.59,7.36 19.51,7.36 13.97,11.63 16.56,17.99 10,13.72 3.44,17.99 6.03,11.63 0.49,7.36 7.41,7.36"/>
                              </svg>
                          <?php endfor;
                      } else {
                          for ($i = 0; $i < 5; $i++): ?>
                              <svg class="w-4 h-4 text-gray-300 star-icon" viewBox="0 0 20 20">
                                  <polygon points="10,1 12.59,7.36 19.51,7.36 13.97,11.63 16.56,17.99 10,13.72 3.44,17.99 6.03,11.63 0.49,7.36 7.41,7.36"/>
                              </svg>
                          <?php endfor;
                      }
                      ?>
                    </div>
                    <span class="text-xs text-gray-600">
                      <?= $nombre_avis > 0 ? number_format($note, 1).' ('.$nombre_avis.' avis)' : 'Aucun avis' ?>
                    </span>
                  </div>

                  <div class="flex justify-between items-center text-sm">
                    <span class="text-green-600 font-medium">
                      <?= number_format($prestataire['tarif'] ?? 0, 2) ?> <?= $prestataire['devise'] === 'USD' ? '$' : 'CDF' ?>
                    </span>
                    <span class="text-gray-500">
                      <?= htmlspecialchars($prestataire['ville']) ?>, <?= htmlspecialchars($prestataire['commune']) ?>
                    </span>
                  </div>

                  <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="profil_prestataire.php?id=<?= $prestataire['prestataire_id'] ?>" 
                       class="block text-center bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-2 rounded-lg text-sm font-medium transition">
                      Voir le profil
                    </a>
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
  <footer class="bg-white border-t border-gray-200 py-8">
    <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
      <p>© 2023 ClicService. Tous droits réservés.</p>
    </div>
  </footer>

</body>
</html>