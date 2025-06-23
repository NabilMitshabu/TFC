<?php
session_start();
require_once '../includes/db_connect.php';

// Activer le mode erreur PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée");
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    $errors = [];
    if (empty($email)) $errors['email'] = "L'email est requis";
    if (empty($password)) $errors['password'] = "Le mot de passe est requis";
    
    if (!empty($errors)) {
        throw new Exception(json_encode(['status' => 'error', 'errors' => $errors]));
    }

    // Recherche utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception(json_encode(['status' => 'error', 'errors' => ['email' => "Email non trouvé"]]));
    }

    // Vérification mot de passe
    if (!password_verify($password, $user['password'])) {
        throw new Exception(json_encode(['status' => 'error', 'errors' => ['password' => "Mot de passe incorrect"]]));
    }

    // Vérification client
    $stmtClient = $pdo->prepare("SELECT * FROM Client WHERE user_id = ?");
    $stmtClient->execute([$user['id']]);
    $client = $stmtClient->fetch();

    if (!$client) {
        throw new Exception(json_encode([
            'status' => 'error', 
            'message' => "Accès réservé aux clients"
        ]));
    }

    // Session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'nom' => $user['nom'],
        'email' => $user['email'],
        'telephone' => $client['telephone'],
        'ville' => $client['ville'],
        'commune' => $client['commune']
    ];

    echo json_encode([
        'status' => 'success',
        'message' => "Connexion réussie !",
        'redirect' => '../views/profilClient.php'
    ]);

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => "Erreur de base de données"
    ]);
} catch (Exception $e) {
    $decoded = json_decode($e->getMessage());
    echo $decoded ? $e->getMessage() : json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}