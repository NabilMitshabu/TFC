<?php
session_start();
require_once '../includes/db_connect.php';

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Récupérer le type d'utilisateur (client ou prestataire)
            $stmt = $pdo->prepare("SELECT id FROM prestataires WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $isPrestataire = $stmt->fetch();

            $_SESSION['user'] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'email' => $user['email'],
                'role' => $isPrestataire ? 'prestataire' : 'client',
                'prestataire_id' => $isPrestataire ? $isPrestataire['id'] : null,
                'logged_in' => true
            ];

            $response = [
                'status' => 'success',
                'message' => 'Connexion réussie!',
                'redirect' => $_SESSION['user']['role'] === 'prestataire' ? 'dashboard-prestataire.php' : 'profilClient.php'
            ];
        } else {
            $response['message'] = 'Email ou mot de passe incorrect';
        }
    } catch (PDOException $e) {
        error_log("Erreur de connexion: " . $e->getMessage());
        $response['message'] = 'Erreur technique. Veuillez réessayer plus tard.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>