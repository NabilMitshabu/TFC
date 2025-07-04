<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: signInClient.php');
    exit;
}

$demande_id = $_GET['demande_id'] ?? null;

if (!$demande_id) {
    header('Location: dashboardClient.php');
    exit;
}

// Vérifier que la demande appartient bien au client et n'a pas encore été évaluée
$stmt = $pdo->prepare("
    SELECT e.*, u.nom AS presta_nom, s.nom AS service_nom
    FROM evaluations e
    JOIN prestataires p ON e.prestataire_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN services s ON e.service_id = s.id
    WHERE e.demande_id = ? AND e.user_id = ? AND e.note IS NULL
");
$stmt->execute([$demande_id, $_SESSION['user']['id']]);
$evaluation = $stmt->fetch();

if (!$evaluation) {
    header('Location: dashboardClient.php');
    exit;
}

// Traitement du formulaire d'évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = $_POST['note'] ?? null;
    $commentaire = $_POST['commentaire'] ?? '';
    
    if ($note && is_numeric($note) && $note >= 1 && $note <= 5) {
        try {
            $pdo->beginTransaction();
            
            // Mettre à jour l'évaluation
            $stmt = $pdo->prepare("
                UPDATE evaluations 
                SET note = ?, commentaire = ?, date_evaluation = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$note, $commentaire, $evaluation['id']]);
            
            // Marquer la notification comme lue (si vous avez une colonne 'lue')
            // $stmt = $pdo->prepare("UPDATE notifications SET lue = 1 WHERE user_id = ? AND lien = ?");
            // $stmt->execute([$_SESSION['user']['id'], "/evaluation.php?demande_id={$demande_id}"]);
            
            $pdo->commit();
            
            $_SESSION['success'] = "Merci pour votre évaluation !";
            header("Location: dashboardClient.php");
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    } else {
        $error = "Veuillez donner une note entre 1 et 5 étoiles";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluer le service - ClicService</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-md">
            <h1 class="text-2xl font-bold text-center mb-6">Évaluer le service</h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-6">
                <p class="text-gray-700 mb-2">
                    Service : <span class="font-semibold"><?= htmlspecialchars($evaluation['service_nom']) ?></span>
                </p>
                <p class="text-gray-700">
                    Prestataire : <span class="font-semibold"><?= htmlspecialchars($evaluation['presta_nom']) ?></span>
                </p>
            </div>
            
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Note (1 à 5 étoiles)</label>
                    <div class="flex justify-center space-x-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" onclick="setRating(<?= $i ?>)" class="text-3xl focus:outline-none">
                                <span class="material-icons star-rating" data-rating="<?= $i ?>">
                                    <?= ($i <= ($_POST['note'] ?? 0)) ? 'star' : 'star_border' ?>
                                </span>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="note" id="rating-value" value="<?= $_POST['note'] ?? 0 ?>">
                </div>
                
                <div class="mb-6">
                    <label for="commentaire" class="block text-gray-700 mb-2">Commentaire (optionnel)</label>
                    <textarea id="commentaire" name="commentaire" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
                </div>
                
                <div class="flex justify-end">
                    <a href="profilClient.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md mr-2 hover:bg-gray-300">Plus tard</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Envoyer l'évaluation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setRating(rating) {
            document.getElementById('rating-value').value = rating;
            
            const stars = document.querySelectorAll('.star-rating');
            stars.forEach(star => {
                const starRating = parseInt(star.getAttribute('data-rating'));
                if (starRating <= rating) {
                    star.textContent = 'star';
                } else {
                    star.textContent = 'star_border';
                }
            });
        }
    </script>
</body>
</html>