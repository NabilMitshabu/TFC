<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil du Prestataire</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Header -->
 <header class="bg-white text-gray-800 shadow-md fixed w-full top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-blue-600">ClicService</h1>
      <nav class="space-x-6 text-sm font-medium text-gray-600">
        <a href="/index.php" class="hover:text-blue-500">Accueil</a>
        <a href="#" class="hover:text-blue-500">Connexion</a>
        <a href="#" class="hover:text-blue-500">Inscription</a>
      </nav>
    </div>
  </header>

  <main class="max-w-5xl mx-auto mt-32 mb-20 px-4">
    <!-- Profile Card -->
    <div class="bg-white shadow-xl rounded-xl p-6">
      <div class="flex flex-col md:flex-row md:items-center md:space-x-6 text-center md:text-left">
        <img src="https://i.pravatar.cc/150?img=13" alt="prestataire" class="w-32 h-32 mx-auto md:mx-0 rounded-full border-4 border-white shadow-md">
        <div class="mt-4 md:mt-0">
          <h2 class="text-3xl font-bold">Alex Services</h2>
          <p class="text-gray-600"> Ville : Paris</p>
          <p class="text-gray-600"> Téléphone : +33 6 12 34 56 78</p>
          <p class="text-yellow-500 mt-1">★★★★☆ (4.5 / 5)</p>
        </div>
      </div>

      <!-- Présentation -->
      <div class="mt-8">
        <h3 class="text-xl font-semibold mb-2">À propos</h3>
        <p class="text-gray-700 leading-relaxed">
          Je suis un professionnel avec plus de 8 ans d'expérience dans les petits travaux, plomberie et installations domestiques. Je suis ponctuel, fiable et soucieux du détail. Ma mission est de garantir votre satisfaction.
        </p>
      </div>

      <!-- Tarifs et Services -->
      <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Services et Tarifs</h3>
        <ul class="space-y-2 text-gray-700">
          <li><span class="font-medium">Menage :</span> 25€</li>
          <li><span class="font-medium">Bricolage :</span> 20€</li>
        </ul>
      </div>

      <!-- Bouton de demande -->
      <div class="mt-10 text-center">
        <a href="/views/demandeService.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition">
          Demander un service
        </a>
      </div>
    </div>
  </main>

</body>
</html>