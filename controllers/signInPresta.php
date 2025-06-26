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
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit();
    }

    // Recherche utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['status' => 'error', 'errors' => ['email' => "Email non trouvé"]]);
        exit();
    }

    // Vérification mot de passe
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'error', 'errors' => ['password' => "Mot de passe incorrect"]]);
        exit();
    }

    // Vérification prestataire
    $stmtPresta = $pdo->prepare("SELECT * FROM prestataires WHERE user_id = ?");
    $stmtPresta->execute([$user['id']]);
    $prestataire = $stmtPresta->fetch();

    if (!$prestataire) {
        echo json_encode([
            'status' => 'error',
            'message' => "Accès réservé aux prestataires"
        ]);
        exit();
    }

    // Vérification si le compte est validé
    if ($prestataire['etat_compte'] !== 'Validé') {
        echo json_encode([
            'status' => 'error',
            'message' => "Votre compte n'a pas encore été validé par l'administration"
        ]);
        exit();
    }

    // Définir la session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'prestataire_id' => $prestataire['id'],
        'nom' => $user['nom'],
        'email' => $user['email'],
        'type' => 'prestataire',
        'telephone' => $prestataire['telephone'],
        'ville' => $prestataire['ville'],
        'commune' => $prestataire['commune'],
        'photo_profil' => $prestataire['photo_profil'],
        'etat_compte' => $prestataire['etat_compte']
    ];

    // Succès
    echo json_encode([
        'status' => 'success',
        'message' => "Connexion réussie",
        'redirect' => '/views/dashboard-prestataire.php' // <-- chemin absolu conseillé
    ]);

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => "Erreur de base de données"
    ]);
}
