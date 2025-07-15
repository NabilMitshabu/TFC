<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user']['prestataire_id'])) {
    header('Location: /views/signInClient.php');
    exit;
}

$prestataire_id = $_SESSION['user']['prestataire_id'];
$image_id = $_GET['id'] ?? null;

if ($image_id) {
    try {
        // Vérifier que l'image appartient bien au prestataire
        $stmt = $pdo->prepare("SELECT image_path FROM prestataire_images WHERE id = ? AND prestataire_id = ?");
        $stmt->execute([$image_id, $prestataire_id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Supprimer le fichier
            $file_path = '../uploads/prestataires/' . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Supprimer l'entrée en base
            $stmt = $pdo->prepare("DELETE FROM prestataire_images WHERE id = ?");
            $stmt->execute([$image_id]);
        }
        
        header("Location: /views/dashboard-prestataire.php#profil");
        exit;
    } catch (PDOException $e) {
        die("Erreur: " . $e->getMessage());
    }
}