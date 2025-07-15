<?php
session_start();
// Pour inscriptionPresta2.php et inscriptionPresta3.php
if (empty($_SESSION['inscription_data']) || $_SESSION['inscription_data']['etape'] < 1) {
    header("Location: inscriptionPresta1.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inscription - Étape 3</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-50">

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
      <h2 class="text-3xl font-bold text-blue-700 mb-6 text-center">Téléverser vos justificatifs</h2>
      
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" action="../controllers/inscriptionPresta.php">
        <div class="mb-4">
          <label class="block font-medium mb-1">Carte d'identité </label>
          <input type="file" name="carte_identite" accept=".jpg,.jpeg,.png,.pdf" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring focus:ring-blue-300" required />
          <p class="text-sm text-gray-500 mt-1">Formats acceptés : JPG, PNG, PDF (max 2MB)</p>
        </div>

        <div class="mb-6">
          <label class="block font-medium mb-1">Photo de profil</label>
          <input type="file" name="photo_profil" accept=".jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring focus:ring-blue-300" required />
          <p class="text-sm text-gray-500 mt-1">Formats acceptés : JPG, PNG (max 2MB)</p>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
          Finaliser l'inscription
        </button>
      </form>
    </div>
  </section>
</body>
</html>