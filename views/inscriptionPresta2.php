<?php
require_once '../includes/db_connect.php';

// Récupérer les catégories de services depuis la base de données
$categoriesQuery = $pdo->query("SELECT DISTINCT nom FROM servicesacc");
$categories = $categoriesQuery->fetchAll(PDO::FETCH_COLUMN);

// Traitement si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville = $_POST['ville'] ?? '';
    $commune = $_POST['commune'] ?? '';
    $servicesJson = $_POST['services'] ?? '[]';

    $services = json_decode($servicesJson, true);

    echo "<h1>Résultat reçu :</h1>";
    echo "<p><strong>Ville :</strong> " . htmlspecialchars($ville) . "</p>";
    echo "<p><strong>Commune :</strong> " . htmlspecialchars($commune) . "</p>";
    echo "<h2>Services :</h2><ul>";
    foreach ($services as $s) {
        echo "<li>" . htmlspecialchars($s['name']) . " - " . intval($s['price']) . " " . htmlspecialchars($s['currency']) . "</li>";
    }
    echo "</ul>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inscription - Étape 2</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <style>
    .service-item { transition: all 0.2s ease; }
    .service-item:hover { background-color: #f8fafc; }
    .remove-service { opacity: 0; transition: opacity 0.2s ease; }
    .service-item:hover .remove-service { opacity: 1; }
  </style>
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
    <h2 class="text-3xl font-bold text-blue-700 mb-6 text-center">Compétences et services</h2>

    <form action="../controllers/inscriptionPresta.php" method="POST">
      <div id="servicesList" class="mb-6 border border-gray-200 rounded-lg divide-y divide-gray-200 max-h-64 overflow-y-auto">
        <div class="p-3 text-center text-gray-500" id="emptyMessage">
          Aucun service ajouté pour le moment
        </div>
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">Catégories de services</label>
        <div class="flex space-x-2">
          <select id="serviceCategory" class="w-1/2 border border-gray-300 rounded-lg h-12 px-3 focus:ring focus:ring-blue-300">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($categories as $categorie): ?>
                <option value="<?= htmlspecialchars($categorie) ?>"><?= htmlspecialchars($categorie) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="number" id="servicePrice" placeholder="Prix" class="w-1/4 border border-gray-300 rounded-lg h-12 px-3 focus:ring focus:ring-blue-300" min="0" step="100" />
          <select id="serviceCurrency" class="w-1/4 border border-gray-300 rounded-lg h-12 px-2 focus:ring focus:ring-blue-300">
            <option value="CDF">CDF</option>
            <option value="USD">USD</option>
          </select>
        </div>
        <div id="customServiceContainer" class="hidden mt-2">
          <input type="text" id="customServiceName" placeholder="Nom du service personnalisé" class="w-full border border-gray-300 rounded-lg h-12 px-3 focus:ring focus:ring-blue-300" />
        </div>
      </div>

      <div class="flex justify-between mb-6">
        <button type="button" id="addServiceBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium transition">
          Ajouter ce service
        </button>
        <input type="hidden" id="servicesData" name="services" value="">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block font-medium mb-1">Ville</label>
          <input type="text" name="ville" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" required />
        </div>
        <div>
          <label class="block font-medium mb-1">Commune</label>
          <input type="text" name="commune" class="w-full border border-gray-300 rounded-lg px-3 h-12 focus:ring focus:ring-blue-300" required />
        </div>
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
        Confirmer
      </button>
    </form>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const services = [];
    const servicesList = document.getElementById('servicesList');
    const emptyMessage = document.getElementById('emptyMessage');
    const serviceCategory = document.getElementById('serviceCategory');
    const servicePrice = document.getElementById('servicePrice');
    const serviceCurrency = document.getElementById('serviceCurrency');
    const customServiceContainer = document.getElementById('customServiceContainer');
    const customServiceName = document.getElementById('customServiceName');
    const addServiceBtn = document.getElementById('addServiceBtn');
    const servicesData = document.getElementById('servicesData');

    serviceCategory.addEventListener('change', function () {
      if (this.value === 'autre') {
        customServiceContainer.classList.remove('hidden');
        customServiceName.required = true;
      } else {
        customServiceContainer.classList.add('hidden');
        customServiceName.required = false;
      }
    });

    addServiceBtn.addEventListener('click', function () {
      const category = serviceCategory.value;
      const price = servicePrice.value.trim();
      const currency = serviceCurrency.value;
      const serviceName = category === 'autre' ? customServiceName.value.trim() : category;

      if (!category || category === '-- Sélectionner --') {
        alert('Veuillez sélectionner une catégorie de service');
        return;
      }

      if (category === 'autre' && !serviceName) {
        alert('Veuillez entrer un nom pour votre service personnalisé');
        return;
      }

      if (!price || isNaN(price) || parseInt(price) <= 0) {
        alert('Veuillez entrer un prix valide');
        return;
      }

      const alreadyExists = services.some(s => s.name.toLowerCase() === serviceName.toLowerCase());
      if (alreadyExists) {
        alert('Ce service a déjà été ajouté.');
        return;
      }

      const service = {
        name: serviceName,
        price: parseInt(price),
        currency: currency,
        category: category === 'autre' ? 'custom' : category
      };

      services.push(service);
      updateServicesList();

      serviceCategory.value = '';
      servicePrice.value = '';
      customServiceName.value = '';
      customServiceContainer.classList.add('hidden');
    });

    function updateServicesList() {
      servicesList.innerHTML = '';
      if (services.length > 0) {
        emptyMessage.classList.add('hidden');

        services.forEach((service, index) => {
          const serviceItem = document.createElement('div');
          serviceItem.className = 'service-item p-3 flex justify-between items-center';
          serviceItem.innerHTML = `
            <div>
              <span class="font-medium">${service.name}</span>
              <span class="text-gray-600 ml-2">- ${service.price.toLocaleString()} ${service.currency}</span>
            </div>
            <button type="button" class="remove-service text-red-500 hover:text-red-700" data-index="${index}">
              Supprimer
            </button>
          `;
          servicesList.appendChild(serviceItem);
        });

        document.querySelectorAll('.remove-service').forEach(btn => {
          btn.addEventListener('click', function () {
            const index = parseInt(this.getAttribute('data-index'));
            services.splice(index, 1);
            updateServicesList();
          });
        });
      } else {
        emptyMessage.classList.remove('hidden');
        servicesList.appendChild(emptyMessage);
      }

      servicesData.value = JSON.stringify(services);
    }
  });
</script>

</body>
</html>