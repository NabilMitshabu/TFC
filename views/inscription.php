<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Choix Connexion</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center px-4 py-10">



  <header class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold text-blue-600">ClicService</h1>
      <nav class="space-x-6 text-sm font-medium text-gray-600">
        <a href="/index.php" class="hover:text-blue-500">Accueil</a>
        <a href="#" class="hover:text-blue-500">Connexion</a>
        <a href="#" class="hover:text-blue-500">Inscription</a>
      </nav>
    </div>
  </header>

  <h1 class="text-3xl font-bold text-blue-800 text-center mb-12">
    Choisissez votre type de compte
  </h1>

  <p class="text-gray-600 text-center mb-10 max-w-xl">
    Inscrivez-vous en tant que <span class="font-semibold">Client</span> pour réserver des services, 
    ou en tant que <span class="font-semibold">Prestataire</span> pour proposer vos services.
  </p>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl w-full px-4">
    <!-- Carte Client -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 shadow hover:shadow-lg transition">
      <h2 class="text-xl font-semibold text-blue-800 mb-2">Client</h2>
      <p class="text-gray-600 mb-4">
        Réservez facilement un professionnel pour vos besoins à domicile 
      </p>
      <a href="inscription-client.html" 
         class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
        Client
      </a>
    </div>

    <!-- Carte Prestataire -->
    <div class="bg-gray-100 border border-gray-300 rounded-xl p-6 shadow hover:shadow-lg transition">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Prestataire</h2>
      <p class="text-gray-600 mb-4">
        Proposez vos services à des clients proches de chez vous et développez votre activité facilement.
      </p>
      <a href="inscriptionPresta1.php" 
         class="inline-block bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 transition">
        Prestataire
      </a>
    </div>
  </div>
</body>
</html>
