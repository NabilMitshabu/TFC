<?php
include '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $reason = $_POST['reason'] ?? '';

    // Valider les entrées
    if (!in_array($status, ['Validé', 'Rejeté'])) {
        throw new Exception("Statut invalide");
    }

    // Préparer la requête
    if ($status === 'Validé') {
        $stmt = $pdo->prepare("UPDATE prestataires SET etat_compte = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        $response = ['success' => true, 'message' => 'Prestataire validé avec succès'];
    } 
    elseif ($status === 'Rejeté') {
        $stmt = $pdo->prepare("UPDATE prestataires SET etat_compte = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        $response = ['success' => true, 'message' => 'Prestataire rejeté avec succès'];
    }

} catch (PDOException $e) {
    $response = ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
?>