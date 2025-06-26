<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 'prestataire') {
    header("Location: signInPresta.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = trim($_POST['description']);
    $prestataire_id = $_SESSION['user']['prestataire_id'];

    try {
        $stmt = $pdo->prepare("UPDATE prestataires SET description = ? WHERE id = ?");
        $stmt->execute([$description, $prestataire_id]);
        
        $_SESSION['success_message'] = "Description " . (!empty($description) ? "mise à jour" : "supprimée") . " avec succès";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour: " . $e->getMessage();
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>