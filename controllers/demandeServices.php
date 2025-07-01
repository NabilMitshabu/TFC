<!-- nouvelle_demande.php -->
<?php
require_once 'includes/auth.php';
requireClient();

// Vérifier si le prestataire_id est passé en paramètre
$prestataire_id = $_GET['prestataire_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO demandes_services 
            (date_heure_rdv, lieu, etat, service_id, description, date_demande, user_id)
            VALUES (?, ?, 'En attente', ?, ?, NOW(), ?)
        ");
        
        $stmt->execute([
            $_POST['date'] . ' ' . $_POST['heure'],
            $_POST['lieu'],
            $_POST['service'],
            $_POST['message'],
            $_SESSION['user']['id'] // Garanti non-null car utilisateur connecté
        ]);
        
        header("Location: mes_demandes.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la création de la demande";
    }
}
?>