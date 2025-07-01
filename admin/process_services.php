<?php
include '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Masquer un service
    if (isset($_GET['masquer'])) {
        $id = $_GET['masquer'];
        $stmt = $pdo->prepare("UPDATE servicesacc SET est_actif = FALSE WHERE id = ?");
        $stmt->execute([$id]);
        $response = ['success' => true, 'message' => 'Service masqué avec succès'];
    }
    
    // Afficher un service
    elseif (isset($_GET['afficher'])) {
        $id = $_GET['afficher'];
        $stmt = $pdo->prepare("UPDATE servicesacc SET est_actif = TRUE WHERE id = ?");
        $stmt->execute([$id]);
        $response = ['success' => true, 'message' => 'Service affiché avec succès'];
    }
    
    // Supprimer un service
    elseif (isset($_GET['supprimer'])) {
        $id = $_GET['supprimer'];
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $response = ['success' => true, 'message' => 'Service supprimé avec succès'];
    }
    
      // Ajouter un service
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'], $_POST['icone'])) {
        $nom = trim($_POST['nom']);
        $icone = trim($_POST['icone']);
        
        // Validation simple
        if (empty($nom)) {
            throw new Exception("Le nom du service ne peut pas être vide");
        }
        
        if (empty($icone)) {
            throw new Exception("Le code SVG de l'icône ne peut pas être vide");
        }
        
        // Insérer dans la base de données
        $stmt = $pdo->prepare("INSERT INTO servicesacc (nom, icone, est_actif) VALUES (?, ?, TRUE)");
        $stmt->execute([$nom, $icone]);
        
        // Récupérer le service nouvellement créé
        $serviceId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM servicesacc WHERE id = ?");
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'message' => 'Service ajouté avec succès',
            'service' => $service
        ];
    }
    
} catch (PDOException $e) {
    $response = ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
}

echo json_encode($response);
exit;
?>