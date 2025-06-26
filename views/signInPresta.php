<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion Prestataire</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-50">

  <section class="min-h-screen flex items-center justify-center px-4 py-8 pt-24">
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md">
      <h2 class="text-3xl font-bold text-blue-700 mb-6 text-center">Connexion Prestataire</h2>

      <div id="error-message" class="hidden bg-red-100 text-red-700 p-3 mb-4 rounded"></div>

      <form id="loginForm" method="POST">
        <div class="mb-4">
          <label for="email" class="block font-medium mb-1">E-mail</label>
          <input type="email" id="email" name="email" required
                 class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300">
        </div>
        <div class="mb-4">
          <label for="password" class="block font-medium mb-1">Mot de passe</label>
          <input type="password" id="password" name="password" required
                 class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300">
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
          Se connecter
        </button>
      </form>
    </div>
  </section>

  <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const errorDiv = document.getElementById('error-message');
      errorDiv.classList.add('hidden');
      errorDiv.textContent = "";

      const formData = new FormData(this);

      try {
        const response = await fetch('../controllers/signInPresta.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        console.log(data); // Pour debug

        if (data.status === 'success') {
          window.location.href = data.redirect;
        } else {
          errorDiv.textContent = data.message || "Erreur de connexion";
          errorDiv.classList.remove('hidden');
        }

      } catch (error) {
        errorDiv.textContent = "Erreur réseau. Veuillez réessayer.";
        errorDiv.classList.remove('hidden');
        console.error(error);
      }
    });
  </script>
</body>
</html>
