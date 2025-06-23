<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - Étape 1</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class=" text-gray-800">

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

  <section class="min-h-screen flex items-center justify-center px-4 py-8 pt-24">
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-2xl">
      <h2 class="text-3xl font-bold text-blue-700 mb-6 text-center">Créer un compte Prestataire</h2>
      
      <?php session_start(); ?>
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <form action="../controllers/inscriptionPresta.php" method="POST">
        <div class="mb-4">
          <label class="block font-semibold text-gray-700 mb-2">Vous êtes :</label>
          <div class="flex space-x-6">
            <label class="flex items-center space-x-2">
              <input type="radio" name="profil" value="particulier" class="accent-blue-600" required />
              <span>Particulier</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="radio" name="profil" value="entreprise" class="accent-blue-600" />
              <span>Entreprise</span>
            </label>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block font-medium mb-1">Nom complet / Entreprise</label>
            <input type="text" name= "nom" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" required />
          </div>
          <div>
            <label class="block font-medium mb-1">Prénom</label>
            <input type="text" name="prenom" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block font-medium mb-1">E-mail</label>
            <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" required />
          </div>
          <div>
            <label class="block font-medium mb-1">Téléphone</label>
            <input type="tel" name="telephone" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" required />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block font-medium mb-1">Mot de passe</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" required />
          </div>
          <div>
            <label class="block font-medium mb-1">Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" required />
          </div>
        </div>
          <button type="submit" class="...">
            Confirmer
          </button>
      </form>
    </div>
  </section>
</body>
</html>