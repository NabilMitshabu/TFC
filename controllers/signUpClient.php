<?php
// Démarrage session si besoin
session_start();

// Connexion à la base de données
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et sécurisation des données
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $telephone = htmlspecialchars(trim($_POST['phone']));
    $commune = htmlspecialchars(trim($_POST['commune']));
    $ville = htmlspecialchars(trim($_POST['ville']));

    // Vérification des champs requis
    if (empty($nom) || empty($email) || empty($_POST['password']) || empty($telephone) || empty($commune) || empty($ville)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header('Location: ../views/signup.php');
        exit();
    }

    try {
        // Préparer et insérer dans la table users
        $stmtUser = $pdo->prepare("INSERT INTO users (nom, email, password) VALUES (?, ?, ?)");
        $stmtUser->execute([$nom, $email, $password]);

        // Récupérer l'ID du nouvel utilisateur
        $userId = $pdo->lastInsertId();

        // Insérer dans la table client
        $stmtClient = $pdo->prepare("INSERT INTO client (user_id, telephone, commune, ville) VALUES (?, ?, ?, ?)");
        $stmtClient->execute([$userId, $telephone, $commune, $ville]);

        // Redirection ou message
        $_SESSION['success'] = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
        header('Location: ../views/login.php');
        exit();

    } catch (PDOException $e) {
        // Gérer les erreurs
        $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
        header('Location: ../index.php');
        exit();
    }
} else {
    // Accès direct interdit
    header('Location: ../index.php');
    exit();
}
?>
