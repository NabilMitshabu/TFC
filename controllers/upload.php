<?php
function uploadFile($file, $target_dir) {
    $result = ['success' => false, 'error' => '', 'file_name' => ''];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Erreur lors du téléchargement du fichier';
        return $result;
    }
    
    // Vérification du type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        $result['error'] = 'Type de fichier non autorisé';
        return $result;
    }
    
    // Vérification de la taille (max 2MB)
    if ($file['size'] > 2097152) {
        $result['error'] = 'Le fichier est trop volumineux (max 2MB)';
        return $result;
    }
    
    // Génération d'un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('service_') . '.' . $extension;
    $target_path = $target_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $result['success'] = true;
        $result['file_name'] = $file_name;
    } else {
        $result['error'] = 'Erreur lors de l\'enregistrement du fichier';
    }
    
    return $result;
}