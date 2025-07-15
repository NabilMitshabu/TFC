<?php
session_start();
require_once '../includes/db_connect.php';

// Étape 1 : Infos personnelles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Validation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas";
        header("Location: ../views/inscriptionPresta1.php");
        exit;
    }

    // Stockage en session
    $_SESSION['inscription_data'] = [
        'etape' => 1,
        'nom' => trim($_POST['nom']),
        'prenom' => trim($_POST['prenom']),
        'email' => trim($_POST['email']),
        'telephone' => trim($_POST['telephone']),
        'motdepasse' => password_hash(trim($_POST['password']), PASSWORD_DEFAULT),
        'type_prestataire' => $_POST['profil']
    ];

    header("Location: ../views/inscriptionPresta2.php");
    exit;
}

// Étape 2 : Services
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ville'])) {
    error_log("Données reçues: " . print_r($_POST, true));
    
    $services = json_decode($_POST['services'], true);
    if (empty($services) || !is_array($services)) {
    $_SESSION['error'] = "Aucun service n'a été ajouté. Veuillez en ajouter au moins un.";
    header("Location: ../views/inscriptionPresta2.php");
    exit;
}

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erreur de décodage JSON: " . json_last_error_msg());
        $_SESSION['error'] = "Format des services invalide";
        header("Location: ../views/inscriptionPresta2.php");
        exit;
    }

    $_SESSION['inscription_data']['etape'] = 2;
    $_SESSION['inscription_data']['ville'] = trim($_POST['ville']);
    $_SESSION['inscription_data']['commune'] = trim($_POST['commune']);
    $_SESSION['inscription_data']['services'] = $services;

    error_log("Services data: " . print_r($_SESSION['inscription_data']['services'], true));

    header("Location: ../views/inscriptionPresta3.php");
    exit;
}

// Étape 3 : Fichiers et enregistrement final
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['carte_identite'])) {
    try {
        $pdo->beginTransaction();

        // 1. Enregistrement utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['inscription_data']['nom'],
            $_SESSION['inscription_data']['prenom'],
            $_SESSION['inscription_data']['email'],
            $_SESSION['inscription_data']['motdepasse']
        ]);
        $user_id = $pdo->lastInsertId();

        // 2. Enregistrement prestataire
        $stmt = $pdo->prepare("INSERT INTO prestataires 
                             (user_id, type_prestataire, telephone, ville, commune, etat_compte) 
                             VALUES (?, ?, ?, ?, ?, 'En attente')");
        $stmt->execute([
            $user_id,
            $_SESSION['inscription_data']['type_prestataire'],
            $_SESSION['inscription_data']['telephone'],
            $_SESSION['inscription_data']['ville'],
            $_SESSION['inscription_data']['commune']
        ]);
        $prestataire_id = $pdo->lastInsertId();

        // 3. Enregistrement services
        foreach ($_SESSION['inscription_data']['services'] as $service) {
            $isCustom = ($service['category'] ?? '') === 'custom';
            
            $stmt = $pdo->prepare("INSERT INTO services 
                                (nom, prestataire_id, description, tarif, devise, is_custom) 
                                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $service['name'],
                $prestataire_id,
                $service['description'] ?? null,
                $service['price'],
                $service['currency'],
                $isCustom ? 1 : 0
            ]);
        }

        // 4. Gestion des fichiers
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $carteName = uniqid('id_') . '_' . basename($_FILES['carte_identite']['name']);
        $photoName = uniqid('photo_') . '_' . basename($_FILES['photo_profil']['name']);

        if (move_uploaded_file($_FILES['carte_identite']['tmp_name'], $uploadDir . $carteName) && 
            move_uploaded_file($_FILES['photo_profil']['tmp_name'], $uploadDir . $photoName)) {
            
            $stmt = $pdo->prepare("UPDATE prestataires SET carte_identite = ?, photo_profil = ? WHERE id = ?");
            $stmt->execute([$carteName, $photoName, $prestataire_id]);

            $pdo->commit();
        

            // Sauvegarder les données dans des variables AVANT de supprimer la session temporaire
            $nom = $_SESSION['inscription_data']['nom'];
            $prenom = $_SESSION['inscription_data']['prenom'];
            $email = $_SESSION['inscription_data']['email'];

            // Maintenant on peut supprimer
            unset($_SESSION['inscription_data']);

            // Puis on initialise la session utilisateur avec ces valeurs
            $_SESSION['user'] = [
                'id' => $user_id,
                'prestataire_id' => $prestataire_id,
                'role' => 'prestataire',
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'logged_in' => true
            ];


            // Maintenant que c’est transféré, on peut supprimer
            unset($_SESSION['inscription_data']);

                        
            header("Location: ../views/dashboard-prestataire.php");
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
        header("Location: ../views/inscriptionPresta3.php");
        exit;
    }
}
?>