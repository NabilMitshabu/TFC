<?php
include '../includes/db_connect.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inscriptions_attente_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// En-têtes CSV
fputcsv($output, [
    'Nom', 
    'Type', 
    'Email', 
    'Téléphone',
    'Services',
    'Ville',
    'Commune',
    'Date Inscription'
]);

// Données
$stmt = $pdo->query("
    SELECT u.nom, p.type_prestataire, u.email, p.telephone, 
           GROUP_CONCAT(s.nom SEPARATOR ', ') as services,
           p.ville, p.commune, p.date_inscription
    FROM prestataires p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN services s ON s.prestataire_id = p.id
    WHERE p.etat_compte = 'En attente'
    GROUP BY p.id
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>