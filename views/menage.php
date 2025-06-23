<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Prestataires de Ménage</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold text-blue-600">ClicService</h1>
      <nav class="space-x-6 text-sm font-medium text-gray-600">
        <a href="/index.php" class="hover:text-blue-500">Accueil</a>
        <a href="#" class="hover:text-blue-500">Connexion</a>
        <a href="/views/inscription.php" class="hover:text-blue-500">Inscription</a>
      </nav>
    </div>
  </header>

  <!-- Titre -->
  <div class="pt-28 pb-6 text-center">
    <h2 class="text-4xl font-bold text-blue-600">Nos prestataires de ménage</h2>
    <p class="text-gray-600 mt-2">Des professionnels qualifiés pour entretenir votre intérieur</p>
  </div>

  <!-- Cartes -->
  <div class="max-w-6xl px-4 mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 pb-16">
    
    <!-- Carte 1 -->
    <div class="bg-white rounded-3xl shadow-lg p-6 transform hover:shadow-xl hover:-translate-y-2 transition duration-300 group relative">
      <div class="flex justify-center -mt-10 mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/219/219969.png" alt="Jean Dupont"
             class="w-28 h-28 rounded-full object-cover border-4 border-blue-500 group-hover:scale-105 transition duration-300">
      </div>
      <h2 class="text-xl font-semibold text-center text-gray-800 mb-2">Jean Dupont</h2>
      <div class="flex justify-center items-center mb-2">
        <div class="flex">
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'>
            <polygon points='10,1 12.59,7.36 19.51,7.36 13.97,11.63 16.56,17.99 10,13.72 3.44,17.99 6.03,11.63 0.49,7.36 7.41,7.36'/>
          </svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-gray-300' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
        </div>
        <span class="ml-2 text-sm text-gray-600 font-medium">4.0/5</span>
      </div>
      <p class="text-center text-green-600 font-semibold mb-1">20,00 € / heure</p>
      <p class="text-center text-gray-500">Paris</p>
      <div class="text-center mt-4">
        <a href="/views/profilPresta.php" class="bg-blue-500 text-white font-semibold px-5 py-2 rounded-full hover:bg-blue-600 transition duration-300 inline-block">
          Voir le profil
        </a>
      </div>
    </div>

    <!-- Carte 2 -->
    <div class="bg-white rounded-3xl shadow-lg p-6 transform hover:shadow-xl hover:-translate-y-2 transition duration-300 group relative">
      <div class="flex justify-center -mt-10 mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/2922/2922506.png" alt="Sophie Martin"
             class="w-28 h-28 rounded-full object-cover border-4 border-blue-500 group-hover:scale-105 transition duration-300">
      </div>
      <h2 class="text-xl font-semibold text-center text-gray-800 mb-2">Sophie Martin</h2>
      <div class="flex justify-center items-center mb-2">
        <div class="flex">
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
        </div>
        <span class="ml-2 text-sm text-gray-600 font-medium">5.0/5</span>
      </div>
      <p class="text-center text-green-600 font-semibold mb-1">22,50 € / heure</p>
      <p class="text-center text-gray-500">Lyon</p>
      <div class="text-center mt-4">
        <button class="bg-blue-500 text-white font-semibold px-5 py-2 rounded-full hover:bg-blue-600 transition duration-300">
          Voir le profil
        </button>
      </div>
    </div>

    <!-- Carte 3 -->
    <div class="bg-white rounded-3xl shadow-lg p-6 transform hover:shadow-xl hover:-translate-y-2 transition duration-300 group relative">
      <div class="flex justify-center -mt-10 mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/147/147144.png" alt="Ali Ben"
             class="w-28 h-28 rounded-full object-cover border-4 border-blue-500 group-hover:scale-105 transition duration-300">
      </div>
      <h2 class="text-xl font-semibold text-center text-gray-800 mb-2">Ali Ben</h2>
      <div class="flex justify-center items-center mb-2">
        <div class="flex">
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-yellow-400' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-gray-300' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
          <svg class='w-5 h-5 text-gray-300' fill='currentColor' viewBox='0 0 20 20'><use href="#star" /></svg>
        </div>
        <span class="ml-2 text-sm text-gray-600 font-medium">3.0/5</span>
      </div>
      <p class="text-center text-green-600 font-semibold mb-1">18,00 € / heure</p>
      <p class="text-center text-gray-500">Marseille</p>
      <div class="text-center mt-4">
        <button class="bg-blue-500 text-white font-semibold px-5 py-2 rounded-full hover:bg-blue-600 transition duration-300">
          Voir le profil
        </button>
      </div>
    </div>

  </div>

</body>
</html>
