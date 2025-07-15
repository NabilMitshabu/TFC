<?php
session_start();
require_once '../includes/db_connect.php';

$prestataire_id = $_SESSION['user']['prestataire_id'];
$service_id = $_POST['service_id'] ?? null;

// Déterminer le nom du service
$nom = $_POST['nom_standard'] === 'autre'
    ? trim($_POST['nom_custom'] ?? '')
    : trim($_POST['nom_standard'] ?? '');

$description = trim($_POST['description'] ?? '');
$tarif = $_POST['tarif'] ?? null;
$devise = $_POST['devise'] ?? null;

if ($service_id) {
    // Mise à jour
    $stmt = $pdo->prepare("UPDATE services SET nom = ?, description = ?, tarif = ?, devise = ? WHERE id = ? AND prestataire_id = ?");
    $stmt->execute([$nom, $description, $tarif, $devise, $service_id, $prestataire_id]);
}

header("Location: ../views/dashboard-prestataire.php");
exit;