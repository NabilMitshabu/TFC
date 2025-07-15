<?php
function uploadFile($file, $target_dir) {
    $result = [
        'success' => false,
        'error' => '',
        'file_name' => ''
    ];

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Erreur lors du téléchargement du fichier. Code: ' . $file['error'];
        return $result;
    }

    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        $result['error'] = 'Type de fichier non autorisé. Seuls JPEG, PNG et GIF sont acceptés.';
        return $result;
    }

    // Vérifier la taille (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        $result['error'] = 'Le fichier est trop volumineux (max 5MB).';
        return $result;
    }

    // Créer le dossier s'il n'existe pas
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Générer un nom de fichier unique
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('img_') . '.' . $file_ext;
    $target_path = $target_dir . $file_name;

    // Déplacer le fichier uploadé
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $result['success'] = true;
        $result['file_name'] = $file_name;
    } else {
        $result['error'] = 'Erreur lors de l\'enregistrement du fichier.';
    }

    return $result;
}