
<?php
include 'includes/db_connect.php';

// Récupération des services pour la section principale
$servicesQuery = $pdo->query("SELECT * FROM servicesacc WHERE est_actif = TRUE ORDER BY nom");
$services = $servicesQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupération des services pour la bannière animée (5 aléatoires)
$bannerServicesQuery = $pdo->query("SELECT nom FROM servicesacc WHERE est_actif = TRUE ORDER BY RAND() LIMIT 5");
$bannerServices = $bannerServicesQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil - Services à domicile</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
        .service-card {
            transition: all 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
  <!-- ... (header et autres sections identiques) ... -->
     <!-- Header -->
<header class="bg-blue-600 fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <a href="/index.php" class="text-xl font-bold text-white hover:text-blue-200 transition">ClicService</a>
        
        <nav class="flex items-center space-x-6 text-sm font-medium text-white">
            <a href="/index.php" class="hover:text-blue-200 transition">Accueil</a>
            
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Menu utilisateur connecté -->
                <div class="relative group">
                    <button class="flex items-center space-x-2 focus:outline-none hover:text-blue-200 transition">
                        <span class="hidden md:inline"><?= htmlspecialchars($_SESSION['user']['prenom']) ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['prenom'] . '+' . $_SESSION['user']['nom']) ?>&background=ffffff&color=3b82f6" 
                             alt="Profil" class="w-8 h-8 rounded-full border-2 border-white">
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block border border-gray-100">
                        <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition">Tableau de bord</a>
                        <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition">Mon profil</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition">Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Menu visiteur -->
                <a href="views/signInClient.php" class="hover:text-blue-200 transition">Connexion</a>
                <a href="views/inscription.php" class="hover:text-blue-200 transition">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

  <!-- Section Services -->
    <section class="py-12 mt-12 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl text-white font-bold text-center mb-8">Nos services à domicile</h2>
            
            <!-- Conteneur blanc avec ombre -->
          <div class="rounded-xl shadow-sm p-6">
                <!-- Liste des services scrollable -->
                <div class="relative">
                    <div class="flex overflow-x-auto hide-scrollbar pb-4">
                        <div class="flex space-x-6">
                            <!-- Dans la boucle des services, modifiez le lien pour inclure l'ID du service -->
                              <!-- Dans la boucle des services, modifiez le lien pour inclure l'ID du service -->
                                <?php foreach ($services as $service): ?>
                                <div class="flex-shrink-0 w-40">
                                    <a href="/views/prestataires.php?service_id=<?= $service['id'] ?>" class="block">
                                        <div class="service-card bg-white rounded-lg border border-gray-200 p-4 text-center hover:border-blue-300">
                                            <div class="bg-blue-50 p-3 rounded-full w-14 h-14 flex items-center justify-center mx-auto mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                                    <?= $service['icone'] ?>
                                                </svg>
                                            </div>
                                            <h4 class="font-medium text-gray-800"><?= htmlspecialchars($service['nom']) ?></h4>
                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

 <!-- Bannière dynamique -->
<section class="flex justify-center py-8 px-4">
  <div class="w-full max-w-4xl bg-blue-500 rounded-t-2xl shadow-md text-center p-6 border border-blue-600">
    <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">
      Tous vos services à domicile réunis ici
    </h1>
    <p class="text-blue-100 text-base md:text-lg mb-4">
      Réservez un professionnel pour
    </p>
    <div class="bg-white px-4 py-2 rounded-full shadow-sm inline-flex items-center gap-2 animate-pulse border border-blue-200">
      <svg id="banner-icon" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"></svg>
      <span id="banner-service" class="text-blue-700 font-semibold text-sm md:text-base">Chargement...</span>
    </div>
  </div>
</section>

  <!-- Catégories populaires -->
  <section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-4">
      <h3 class="text-3xl font-bold mb-10 text-center">Nos catégories populaires</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        <?php 
        $popularServices = array_slice($services, 0, 3); // Prendre les 3 premiers services
        foreach ($popularServices as $service): 
        ?>
        <div class="bg-gray-50 p-6 rounded-lg text-center shadow hover:shadow-lg transition transform hover:-translate-y-1 border border-gray-200">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" 
               stroke="currentColor" class="w-10 h-10 mx-auto mb-4 text-blue-600">
            <?= $service['icone'] ?>
          </svg>
          <h4 class="font-semibold text-xl mb-2 text-gray-800"><?= htmlspecialchars($service['nom']) ?></h4>
          <p class="text-gray-600">Des professionnels qualifiés pour vos besoins en <?= htmlspecialchars($service['nom']) ?>.</p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Comment ça marche -->
  <section class="py-12 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
      <h3 class="text-3xl font-bold mb-12 text-center">Comment ça marche ?</h3>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="text-center">
          <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-blue-600 text-2xl font-bold">1</span>
          </div>
          <h4 class="font-semibold text-lg mb-2">Choisissez un service</h4>
          <p class="text-gray-600">Parcourez nos catégories et sélectionnez le service dont vous avez besoin.</p>
        </div>
        <div class="text-center">
          <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-blue-600 text-2xl font-bold">2</span>
          </div>
          <h4 class="font-semibold text-lg mb-2">Réservez en ligne</h4>
          <p class="text-gray-600">Sélectionnez une date et un créneau horaire qui vous conviennent.</p>
        </div>
        <div class="text-center">
          <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-blue-600 text-2xl font-bold">3</span>
          </div>
          <h4 class="font-semibold text-lg mb-2">Profitez du service</h4>
          <p class="text-gray-600">Un professionnel qualifié intervient chez vous à l'heure convenue.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Témoignages -->
  <section class="py-12 bg-white">
    <div class="max-w-5xl mx-auto px-4">
      <h3 class="text-3xl font-bold mb-12 text-center">Ce que disent nos clients</h3>
      <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-gray-50 p-6 rounded-lg shadow-lg border border-gray-200">
          <div class="flex items-center mb-4">
            <img src="https://randomuser.me/api/portraits/women/43.jpg" alt="Marie D." class="w-12 h-12 rounded-full mr-4">
            <div>
              <h4 class="font-semibold">Marie D.</h4>
              <div class="flex text-yellow-400">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <?php endfor; ?>
              </div>
            </div>
          </div>
          <p class="italic text-gray-700">"Super expérience ! Le prestataire était ponctuel et très professionnel. Je recommande vivement ClicService pour tous vos besoins en ménage."</p>
        </div>
        <div class="bg-gray-50 p-6 rounded-lg shadow-lg border border-gray-200">
          <div class="flex items-center mb-4">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Karim B." class="w-12 h-12 rounded-full mr-4">
            <div>
              <h4 class="font-semibold">Karim B.</h4>
              <div class="flex text-yellow-400">
                <?php for ($i = 0; $i < 4; $i++): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <?php endfor; ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
            </div>
          </div>
          <p class="italic text-gray-700">"Je recommande à 100 %. Très pratique et rapide. J'ai trouvé un électricien en moins d'une heure pour un dépannage urgent."</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="py-12 bg-blue-600 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <h3 class="text-3xl font-bold mb-6">Prêt à trouver le service parfait ?</h3>
      <p class="text-xl mb-8">Inscrivez-vous dès maintenant et bénéficiez de 10% sur votre première prestation</p>
      <a href="views/inscription.php" class="bg-white text-blue-600 font-semibold px-8 py-3 rounded-full shadow-lg hover:bg-gray-100 transition duration-300 inline-block">
        S'inscrire gratuitement
      </a>
    </div>
  </section>

 <?php require "Views/portions/footer.php" ?>

  
<script>
  // Liste des services avec leurs icônes (utilisez les icônes de Heroicons)
  const services = [
    { name: "Plomberie", icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />' },
    { name: "Électricité", icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />' },
    { name: "Ménage", icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />' },
    { name: "Jardinage", icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />' },
    { name:'Serrurerie', icon:'<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>'},
    { name:'Cours particuliers', icon:'<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-5.342m0 0A50.716 50.716 0 1012 13.489a50.716 50.716 0 017.74-5.342m0 0a50.669 50.669 0 014.685 5.145"/>'},
    { name:'Informatique', icon:'<path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/>'}
  ];

  let currentService = 0;
  const iconElement = document.getElementById('banner-icon');
  const serviceElement = document.getElementById('banner-service');

  function rotateServices() {
    // Animation de fondu
    serviceElement.style.opacity = '0';
    setTimeout(() => {
      // Changement du service
      currentService = (currentService + 1) % services.length;
      iconElement.innerHTML = services[currentService].icon;
      serviceElement.textContent = services[currentService].name;
      // Réapparition en fondu
      serviceElement.style.opacity = '1';
    }, 500);
  }

  // Démarrer la rotation (toutes les 3 secondes)
  rotateServices(); // Affiche immédiatement le premier service
  setInterval(rotateServices, 3000);
</script>
  </script>
</body>
</html>