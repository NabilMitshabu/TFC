<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Vérifier d'abord si l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] . (isset($_GET['prestataire_id']) ? '?prestataire_id=' . $_GET['prestataire_id'] : '');
    header("Location: /views/signInClient.php");
    exit();
}

// Vérifier si l'ID du prestataire est présent
$prestataire_id = $_GET['prestataire_id'] ?? null;

if (!$prestataire_id) {
    header("Location: /index.php");
    exit();
}

// Récupérer les informations du prestataire et ses services
try {
    // Info du prestataire
    $stmt = $pdo->prepare("
        SELECT p.*, u.nom, u.prenom 
        FROM prestataires p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$prestataire_id]);
    $prestataire = $stmt->fetch();

    if (!$prestataire) {
        throw new Exception("Prestataire non trouvé");
    }

    // Services proposés par ce prestataire
    $stmt = $pdo->prepare("SELECT id, nom FROM services WHERE prestataire_id = ?");
    $stmt->execute([$prestataire_id]);
    $services = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $required_fields = ['service', 'date', 'heure', 'lieu'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ $field est obligatoire";
        }
    }

    if (!isset($errors)) {
        try {
            $date_heure_rdv = $_POST['date'] . ' ' . $_POST['heure'] . ':00';
            
            $stmt = $pdo->prepare("
                INSERT INTO demandes_services 
                (date_heure_rdv, lieu, etat, service_id, description, date_demande, user_id)
                VALUES (?, ?, 'En attente', ?, ?, NOW(), ?)
            ");
            
             // Récupère l'ID de l'utilisateur connecté
            $user_id = getUserId();
           // Dans votre insertion SQL :
            $stmt->execute([
                $date_heure_rdv,
                $_POST['lieu'],
                $_POST['service'],
                $_POST['message'] ?? '',
                $user_id // Utilisation de l'ID utilisateur
            ]);
            
            header("Location: /confirmation-demande.php");
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Demande de Service</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <style>
        .custom-input {
            border: none;
            border-bottom: 1px solid #d1d5db;
            border-radius: 0;
            padding-left: 0;
            padding-right: 0;
            box-shadow: none;
        }
        .custom-input:focus {
            outline: none;
            box-shadow: none;
            border-bottom-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <!-- Header -->
    <?php require "portions/header.php" ?>

    <main class="pt-24 max-w-4xl mx-auto px-4">
        <!-- Indication sur le prestataire -->
        <div class="mb-6 bg-blue-50 p-4 rounded-lg flex items-center">
            <span class="text-blue-700 font-medium">Vous faites une demande à :</span>
            <span class="ml-2 font-semibold"><?= htmlspecialchars($prestataire['prenom'] . ' ' . $prestataire['nom']) ?></span>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-8">
                <h1 class="text-2xl font-bold text-center mb-1">Demande de Service</h1>
                <p class="text-center text-gray-600 mb-8">Remplissez les détails de votre demande</p>

                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                        <?php foreach ($errors as $error): ?>
                            <p><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <input type="hidden" name="prestataire_id" value="<?= $prestataire_id ?>">
                    
                    <!-- Service demandé -->
                    <div>
                        <label for="service" class="block font-medium mb-2">Service</label>
                        <select id="service" name="service" class="w-full custom-input py-2" required>
                            <option value="">-- Sélectionner un service --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['id'] ?>" <?= (isset($_POST['service']) && $_POST['service'] == $service['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($service['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date et heure -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="date" class="block font-medium mb-2">Date</label>
                            <input type="date" id="date" name="date" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>"
                                   min="<?= date('Y-m-d') ?>" class="w-full custom-input py-2" required />
                        </div>
                        <div>
                            <label for="heure" class="block font-medium mb-2">Heure</label>
                            <input type="time" id="heure" name="heure" value="<?= htmlspecialchars($_POST['heure'] ?? '') ?>"
                                   class="w-full custom-input py-2" required />
                        </div>
                    </div>

                    <!-- Lieu -->
                    <div>
                        <label for="lieu" class="block font-medium mb-2">Lieu</label>
                        <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>"
                               placeholder="Commune, Quartier, Avenue, N°" class="w-full custom-input py-2" required />
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block font-medium mb-2">Message</label>
                        <textarea id="message" name="message" rows="3" 
                                  class="w-full custom-input py-2"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <!-- Bouton -->
                    <div class="text-center mt-8">
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg shadow hover:bg-blue-700 transition text-lg font-medium w-full">
                            Envoyer la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>