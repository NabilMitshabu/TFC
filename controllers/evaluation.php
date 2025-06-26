<?php
session_start();
require_once '../includes/db_connect.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

// Vérification CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Erreur de sécurité. Veuillez réessayer.';
    header('Location: dashboard.php');
    exit();
}

// Validation des données
$requiredFields = ['demande_id', 'prestataire_id', 'note'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis.';
        header('Location: dashboard.php');
        exit();
    }
}

$demandeId = (int)$_POST['demande_id'];
$prestataireId = (int)$_POST['prestataire_id'];
$note = (int)$_POST['note'];
$commentaire = !empty($_POST['commentaire']) ? trim($_POST['commentaire']) : null;

// Vérification que la note est entre 1 et 5
if ($note < 1 || $note > 5) {
    $_SESSION['error'] = 'La note doit être entre 1 et 5 étoiles.';
    header('Location: dashboard.php');
    exit();
}

try {
    // Vérification que la demande appartient bien à l'utilisateur
    $stmt = $pdo->prepare("
        SELECT id FROM demandes_services 
        WHERE id = ? AND user_id = ? AND etat = 'Terminée'
    ");
    $stmt->execute([$demandeId, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Demande non trouvée ou non éligible pour évaluation.');
    }

    // Insertion de l'évaluation
    $stmt = $pdo->prepare("
        INSERT INTO evaluations (user_id, prestataire_id, demande_id, note, commentaire, date_evaluation)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $prestataireId,
        $demandeId,
        $note,
        $commentaire
    ]);

    $_SESSION['success'] = 'Merci pour votre évaluation !';
} catch (Exception $e) {
    $_SESSION['error'] = 'Une erreur est survenue: ' . $e->getMessage();
}

header('Location: dashboard.php');
exit();