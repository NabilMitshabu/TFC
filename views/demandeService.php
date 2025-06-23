<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Demande de Service</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  </head>
  <body class="bg-gray-50 text-gray-800">
    <!-- Header -->
    <header class="bg-white shadow-md fixed w-full top-0 z-50">
      <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-blue-600">ClicService</h1>
        <nav class="space-x-6 text-sm font-medium text-gray-600">
          <a href="/index.php" class="hover:text-blue-500">Accueil</a>
          <a href="#" class="hover:text-blue-500">Connexion</a>
          <a href="#" class="hover:text-blue-500">Inscription</a>
        </nav>
      </div>
    </header>

    <!-- Main content -->
<main class="pt-24 max-w-4xl mx-auto px-4">
  <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row">
    
    <!-- Image section (hauteur ajustée) -->
    <div class="md:w-1/2">
      <img
        src="https://th.bing.com/th/id/OIP.IUaEKbf_JeqNy5Lmz2cORQHaE8?w=1920&h=1280&rs=1&pid=ImgDetMain&cb=idpwebp2&o=7&rm=3"
        alt="Service"
        class="w-full h-full object-cover max-h-[300px]"
        loading="lazy"
      />
    </div>

    <!-- Form section avec moins de hauteur et d'espaces -->
    <div class="md:w-1/2 p-4 overflow-y-auto">
      <h2 class="text-xl font-bold text-center mb-2 text-blue-700">Demande Service</h2>

      <form action="#" method="POST" class="space-y-3 text-sm">
        <!-- Nom et Email -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label for="nom" class="block font-medium mb-1">Nom complet</label>
            <input type="text" id="nom" name="nom" class="w-full h-9 border border-gray-300 rounded px-3 shadow-sm" required />
          </div>
          <div>
            <label for="email" class="block font-medium mb-1">E-mail</label>
            <input type="email" id="email" name="email" class="w-full h-9 border border-gray-300 rounded px-3 shadow-sm" required />
          </div>
        </div>

        <!-- Téléphone -->
        <div>
          <label for="telephone" class="block font-medium mb-1">Téléphone</label>
          <input type="tel" id="telephone" name="telephone" class="w-full h-9 border border-gray-300 rounded px-3 shadow-sm" />
        </div>

        <!-- Service demandé -->
        <div>
          <label for="service" class="block font-medium mb-1">Service</label>
          <select id="service" name="service" class="w-full h-9 border border-gray-300 rounded px-3 shadow-sm" required>
            <option value="">-- Sélectionner --</option>
            <option value="fuites">Réparation de fuites</option>
            <option value="meubles">Montage de meubles</option>
            <option value="luminaires">Installation de luminaires</option>
            <option value="robinets">Remplacement de robinets</option>
          </select>
        </div>

        <!-- Date et heure -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label for="date" class="block font-medium mb-1">Date</label>
            <input type="date" id="date" name="date" class="w-full h-9 border border-gray-300 rounded px-3 shadow-sm" required />
          </div>
          <div>
            <label for="heure" class="block font-medium mb-1">Heure</label>
            <input type="time" id="heure" name="heure" class="w-full h-9 border border-gray-300 rounded px-3 shadow-sm" required />
          </div>
        </div>

        <!-- Lieu -->
        <div>
          <label for="lieu" class="block font-medium mb-1">Lieu</label>
          <input type="text" id="lieu" name="lieu" placeholder="Commune, Quartier, Avenue, N°" class="w-full h-9 border border-gray-300 rounded px-3 shadow-sm" required />
        </div>

        <!-- Message -->
        <div>
          <label for="message" class="block font-medium mb-1">Message</label>
          <textarea id="message" name="message" rows="2" class="w-full border border-gray-300 rounded px-3 py-1 shadow-sm"></textarea>
        </div>

        <!-- Bouton -->
        <div class="text-center mt-2">
          <button type="submit" class="bg-blue-600 text-white px-5 py-1.5 rounded shadow hover:bg-blue-700 transition text-sm">
            Envoyer
          </button>
        </div>
      </form>
    </div>
  </div>
</main>


  </body>
</html>
