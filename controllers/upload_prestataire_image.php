<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/upload.php';

if (!isset($_SESSION['user']['prestataire_id'])) {
    header('Location: /views/signInClient.php');
    exit;
}

$prestataire_id = $_SESSION['user']['prestataire_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['images'])) {
    try {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            ];
            
            $upload = uploadFile($file, '../uploads/prestataires/');
            
            if ($upload['success']) {
                $stmt = $pdo->prepare("INSERT INTO prestataire_images (prestataire_id, image_path) VALUES (?, ?)");
                $stmt->execute([$prestataire_id, $upload['file_name']]);
            }
        }
        
        header("Location: /views/dashboard-prestataire.php#profil");
        exit;
    } catch (PDOException $e) {
        die("Erreur: " . $e->getMessage());
    }
}