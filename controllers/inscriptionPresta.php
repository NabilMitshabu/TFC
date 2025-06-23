<?php
session_start();
require_once '../includes/db_connect.php';

// Étape 1 : Infos personnelles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['profil'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $motdepasse = trim($_POST['password'] ?? '');
    $type_prestataire = $_POST['profil'];

    if (empty($nom) || empty($email) || empty($motdepasse) || empty($telephone)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs requis.";
        header("Location: ../views/inscriptionPresta1.php");
        exit;
    }

    try {
        // Vérifier doublon
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $_SESSION['error'] = "Cet email est déjà utilisé.";
            header("Location: ../views/inscriptionPresta1.php");
            exit;
        }

        // Insérer dans users
        $hashedPassword = password_hash($motdepasse, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $hashedPassword]);
        $user_id = $pdo->lastInsertId();

        // Insérer dans prestataires
        $stmt = $pdo->prepare("INSERT INTO prestataires (user_id, type_prestataire, telephone, etat_compte) VALUES (?, ?, ?, 'En attente')");
        $stmt->execute([$user_id, $type_prestataire, $telephone]);

        $_SESSION['user_id'] = $user_id;
        header("Location: ../views/inscriptionPresta2.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
        header("Location: ../views/inscriptionPresta1.php");
        exit;
    }
}

// Étape 2 : Services, ville, commune
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ville'])) {
    $ville = trim($_POST['ville'] ?? '');
    $commune = trim($_POST['commune'] ?? '');
    $servicesJson = $_POST['services'] ?? '[]';
    $services = json_decode($servicesJson, true);

    if (empty($ville) || empty($commune)) {
        $_SESSION['error'] = "Ville et commune obligatoires.";
        header("Location: ../views/inscriptionPresta2.php");
        exit;
    }

    try {
        $userId = $_SESSION['user_id'];

        // Update ville et commune dans prestataires
        $stmt = $pdo->prepare("UPDATE prestataires SET ville = ?, commune = ? WHERE user_id = ?");
        $stmt->execute([$ville, $commune, $userId]);

        // Récupérer l'id du prestataire
        $stmt = $pdo->prepare("SELECT id FROM prestataires WHERE user_id = ?");
        $stmt->execute([$userId]);
        $prestataire = $stmt->fetch();

        if (!$prestataire) {
            throw new Exception("Prestataire introuvable pour l'utilisateur ID $userId.");
        }

        $prestataireId = $prestataire['id'];

        // Enregistrer services
        foreach ($services as $s) {
            $stmt = $pdo->prepare("INSERT INTO services (nom, prestataire_id, tarif, devise) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $s['name'],
                $prestataireId,
                $s['price'],
                $s['currency']
            ]);
        }

        header("Location: ../views/inscriptionPresta3.php");
        exit;

    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        header("Location: ../views/inscriptionPresta2.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: ../views/inscriptionPresta2.php");
        exit;
    }
}


// Étape 3 : Téléversement de fichiers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['carte_identite'])) {
    $userId = $_SESSION['user_id'];
    $uploadDir = '../uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $carte = $_FILES['carte_identite'];
    $photo = $_FILES['photo_profil'];

    $allowedCarte = ['image/jpeg', 'image/png', 'application/pdf'];
    $allowedPhoto = ['image/jpeg', 'image/png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $errors = [];

    // Validation carte d'identité
    if (!in_array($carte['type'], $allowedCarte)) {
        $errors[] = "Le format de la carte d'identité doit être JPG, PNG ou PDF.";
    } elseif ($carte['size'] > $maxFileSize) {
        $errors[] = "La carte d'identité ne doit pas dépasser 2MB.";
    }

    // Validation photo profil
    if (!in_array($photo['type'], $allowedPhoto)) {
        $errors[] = "Le format de la photo de profil doit être JPG ou PNG.";
    } elseif ($photo['size'] > $maxFileSize) {
        $errors[] = "La photo de profil ne doit pas dépasser 2MB.";
    }

    if (empty($errors)) {
        $carteName = uniqid('id_') . '_' . basename($carte['name']);
        $photoName = uniqid('photo_') . '_' . basename($photo['name']);

        if (move_uploaded_file($carte['tmp_name'], $uploadDir . $carteName) && 
            move_uploaded_file($photo['tmp_name'], $uploadDir . $photoName)) {
            
            // Mise à jour dans prestataires
            $stmt = $pdo->prepare("UPDATE prestataires SET carte_identite = ?, photo_profil = ? WHERE user_id = ?");
            $stmt->execute([$carteName, $photoName, $userId]);

            header("Location: ../views/confirmation.php");
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors du téléversement des fichiers.";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }

    header("Location: ../views/inscriptionPresta3.php");
    exit;
}