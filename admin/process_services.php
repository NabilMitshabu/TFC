<?php
include '../includes/db_connect.php';

// Masquer un service (ne plus l'afficher sur la page d'accueil)
if (isset($_GET['masquer'])) {
    $id = $_GET['masquer'];
    $stmt = $pdo->prepare("UPDATE servicesacc SET est_actif = FALSE WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: services.php?success=masque");
    exit;
}

// Afficher un service (le réactiver pour la page d'accueil)
if (isset($_GET['afficher'])) {
    $id = $_GET['afficher'];
    $stmt = $pdo->prepare("UPDATE servicesacc SET est_actif = TRUE WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: services.php?success=affiche");
    exit;
}

// Supprimer définitivement un service (optionnel)
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: services.php?success=suppression");
    exit;
}

// Ajouter un service (reste identique)
if (isset($_POST['ajouter'])) {
    // ... (code existant) ...
}

header("Location: services.php");
exit;
?>