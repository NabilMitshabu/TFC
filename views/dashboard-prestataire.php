<?php
session_start();
require_once '../includes/db_connect.php';

// Vérification de session
if (!isset($_SESSION['user']['id'])) {
    header('Location: signInClient.php');
    exit;
}

// ID du prestataire depuis la session
$prestataire_id = $_SESSION['user']['prestataire_id'];



try {
    // Récupération des infos du prestataire
    $stmt = $pdo->prepare("
        SELECT p.*, u.nom, u.prenom, u.email 
        FROM prestataires p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$prestataire_id]);
    $prestataire = $stmt->fetch();

    // Compter les messages non lus
    $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM messages 
            WHERE recipient_id = ? AND lu = 0
        ");
    $stmt->execute([$_SESSION['user']['id']]);
    $unreadMessages = $stmt->fetchColumn();

    if (!$prestataire) {
        session_destroy();
        header('Location: signInClient.php');
        exit();
    }

    // Fonction pour obtenir le chemin de la photo de profil
    function getProfilePhoto($photo) {
        if (empty($photo)) {
            return 'https://cdn-icons-png.flaticon.com/512/219/219969.png';
        }
        return filter_var($photo, FILTER_VALIDATE_URL) ? $photo : '../uploads/' . $photo;
    }

    $photo_profil = getProfilePhoto($prestataire['photo_profil']);

    // Récupération des demandes en attente
    $stmt = $pdo->prepare("
        SELECT ds.*, u.nom AS client_nom, u.email AS client_email, s.nom AS service_nom 
        FROM demandes_services ds
        JOIN services s ON ds.service_id = s.id
        JOIN users u ON ds.user_id = u.id
        WHERE s.prestataire_id = ? AND ds.etat = 'En attente'
    ");
    $stmt->execute([$prestataire_id]);
    $demandes = $stmt->fetchAll();
    
    // Récupération des tâches en cours
    $stmt = $pdo->prepare("
        SELECT ds.*, u.nom AS client_nom, s.nom AS service_nom 
        FROM demandes_services ds
        JOIN services s ON ds.service_id = s.id
        JOIN users u ON ds.user_id = u.id
        WHERE s.prestataire_id = ? AND ds.etat = 'Validée'
    ");
    $stmt->execute([$prestataire_id]);
    $taches = $stmt->fetchAll();

    // Statistiques
    $stats = [
        'taches_completees' => 0,
        'evaluation_moyenne' => 0
    ];

    // Nombre de tâches validées
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM demandes_services ds
        JOIN services s ON ds.service_id = s.id
        WHERE s.prestataire_id = ? AND ds.etat = 'Terminée'
    ");
    $stmt->execute([$prestataire_id]);
    $stats['taches_completees'] = $stmt->fetchColumn();

    // Moyenne des évaluations
    $stmt = $pdo->prepare("SELECT AVG(note) FROM evaluations WHERE prestataire_id = ?");
    $stmt->execute([$prestataire_id]);
    $stats['evaluation_moyenne'] = round($stmt->fetchColumn() ?? 0, 1);

    // Récupération des dernières tâches terminées avec évaluations
    $stmt = $pdo->prepare("
        SELECT ds.*, u.nom AS client_nom, s.nom AS service_nom, 
               e.note, e.commentaire, e.date_evaluation
        FROM demandes_services ds
        JOIN services s ON ds.service_id = s.id
        JOIN users u ON ds.user_id = u.id
        LEFT JOIN evaluations e ON ds.id = e.demande_id
        WHERE s.prestataire_id = ? AND ds.etat = 'Terminée'
        ORDER BY ds.date_heure_rdv DESC
        LIMIT 3
    ");
    $stmt->execute([$prestataire_id]);
    $taches_terminees = $stmt->fetchAll();


    // Récupération des conversations
       $stmt = $pdo->prepare("
    SELECT DISTINCT u.id AS client_id, u.nom AS client_nom, u.prenom AS client_prenom,
        ANY_VALUE(c.telephone) AS telephone,
        (
            SELECT p.photo_profil 
            FROM prestataires p 
            WHERE p.user_id = u.id 
            LIMIT 1
        ) AS client_photo,
        MAX(m.date_envoi) AS last_message_date
    FROM demandes_services ds
    JOIN services s ON ds.service_id = s.id
    JOIN users u ON ds.user_id = u.id
    LEFT JOIN client c ON u.id = c.user_id
    LEFT JOIN messages m ON 
        (m.sender_id = u.id AND m.recipient_id = :me)
        OR (m.sender_id = :me AND m.recipient_id = u.id)
    WHERE s.prestataire_id = :presta_id AND ds.etat IN ('Validée', 'Terminée')
    GROUP BY u.id
    ORDER BY last_message_date DESC
");
$stmt->execute([
    'me' => $_SESSION['user']['id'],
    'presta_id' => $prestataire_id
]);
$conversations = $stmt->fetchAll();

function getProfileImage($prenom, $nom, $photo = null) {
    if (!empty($photo)) {
        return filter_var($photo, FILTER_VALIDATE_URL) ? $photo : '../uploads/' . $photo;
    }

    $initials = '';
    if (!empty($prenom)) $initials .= substr($prenom, 0, 1);
    if (!empty($nom)) $initials .= substr($nom, 0, 1);

    return "https://ui-avatars.com/api/?name=" . urlencode($initials ?: 'U') . "&background=3b82f6&color=fff";
}




    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $demande_id = $_POST['demande_id'] ?? null;
        
        if ($demande_id && is_numeric($demande_id)) {
            try {
                switch ($_POST['action']) {
                    case 'accepter':
                        $stmt = $pdo->prepare("UPDATE demandes_services SET etat = 'Validée' WHERE id = ?");
                        $stmt->execute([$demande_id]);
                        
                        // Notification au client
                        $stmt = $pdo->prepare("SELECT user_id FROM demandes_services WHERE id = ?");
                        $stmt->execute([$demande_id]);
                        $client_id = $stmt->fetchColumn();
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO notifications 
                            (user_id, titre, message, icon) 
                            VALUES (?, 'Demande acceptée', 'Votre demande #{$demande_id} a été acceptée', 'check_circle')
                        ");
                        $stmt->execute([$client_id]);
                        break;
                        
                    case 'refuser':
                        $stmt = $pdo->prepare("UPDATE demandes_services SET etat = 'Refusée' WHERE id = ?");
                        $stmt->execute([$demande_id]);
                        
                        // Notification au client
                        $stmt = $pdo->prepare("SELECT user_id FROM demandes_services WHERE id = ?");
                        $stmt->execute([$demande_id]);
                        $client_id = $stmt->fetchColumn();
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO notifications 
                            (user_id, titre, message, icon) 
                            VALUES (?, 'Demande refusée', 'Votre demande #{$demande_id} a été refusée', 'cancel')
                        ");
                        $stmt->execute([$client_id]);
                        break;
                        
                    case 'changer_statut':
                        $nouveau_statut = $_POST['statut'] ?? null;
                        if (in_array($nouveau_statut, ['Validée', 'Refusée', 'Terminée'])) {
                            try {
                                // Mise à jour du statut
                                $stmt = $pdo->prepare("UPDATE demandes_services SET etat = ? WHERE id = ?");
                                $stmt->execute([$nouveau_statut, $demande_id]);

                                if ($nouveau_statut === 'Terminée') {
                                    // Envoyer une notification au client
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            ds.user_id AS client_id,
                                            s.prestataire_id,
                                            s.id AS service_id,
                                            s.nom AS service_nom,
                                            u.nom AS client_nom,
                                            p.user_id AS presta_user_id
                                        FROM demandes_services ds
                                        JOIN services s ON ds.service_id = s.id
                                        JOIN prestataires p ON s.prestataire_id = p.id
                                        JOIN users u ON ds.user_id = u.id
                                        WHERE ds.id = ?
                                    ");
                                    $stmt->execute([$demande_id]);
                                    $info = $stmt->fetch();

                                    if ($info) {
                                        // Notification au client avec le nom du service
                                        $message = "Votre demande de service '".htmlspecialchars($info['service_nom'])."' (ID: $demande_id) a été marquée comme terminée";
                                        
                                        $stmt = $pdo->prepare("
                                            INSERT INTO notifications
                                            (user_id, titre, message, icon, date_creation)
                                            VALUES (?, 'Service terminé', ?, 'done_all', NOW())
                                        ");
                                        $insertSuccess = $stmt->execute([
                                            $info['client_id'],
                                            $message
                                        ]);
                                        
                                        if (!$insertSuccess) {
                                            error_log("Échec de l'insertion de la notification pour la demande $demande_id");
                                        } else {
                                            error_log("Notification envoyée avec succès au client ID: ".$info['client_id']);
                                        }

                                        // Création évaluation
                                        $stmt = $pdo->prepare("
                                            INSERT INTO evaluations
                                            (user_id, prestataire_id, demande_id, service_id, date_evaluation)
                                            VALUES (?, ?, ?, ?, NOW())
                                        ");
                                        $stmt->execute([
                                            $info['client_id'],
                                            $info['prestataire_id'],
                                            $demande_id,
                                            $info['service_id']
                                        ]);
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log("Erreur lors du changement de statut: " . $e->getMessage());
                            }
                        }
                        break;
                }
                
                // Rafraîchir les données après modification
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
                
            } catch (PDOException $e) {
                error_log("Erreur: " . $e->getMessage());
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }

} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    // Affichez plus de détails en développement
    if (ini_get('display_errors')) {
        die("Erreur serveur: " . $e->getMessage() . " dans " . $e->getFile() . " ligne " . $e->getLine());
    } else {
        die("Erreur serveur. Veuillez réessayer plus tard.");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Prestataire</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-icons {
            font-family: 'Material Icons';
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            vertical-align: middle;
        }
        .menu-item { transition: all 0.2s ease; }
        .menu-item:hover { transform: translateX(4px); }
        .profile-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .tab-section { display: none; }
        .tab-section.active { display: block; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Header -->
    <?php require "portions/headerUsers.php" ?>

    <main class="min-h-screen flex pt-24 px-4">
        <aside class="w-64 bg-white shadow rounded-lg h-fit sticky top-24 mr-6 hidden md:block">
            <div class="p-6">
                <div class="flex flex-col items-center mb-8">
                    <div class="relative mb-4">
                        <img src="<?= $photo_profil ?>" alt="Photo profil" class="rounded-full w-24 h-24 object-cover profile-shadow">
                        <button class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition transform hover:scale-105">
                            <span class="material-icons text-sm">edit</span>
                        </button>
                    </div>
                    <h2 class="text-xl font-semibold text-center"><?= htmlspecialchars($prestataire['nom']) ?></h2>
                    <p class="text-sm text-gray-500 text-center"><?= htmlspecialchars($prestataire['email']) ?></p>
                </div>

                <nav class="space-y-1">
                    <a href="#" onclick="showTab('accueil')" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 text-blue-600 font-medium menu-item">
                        <span class="material-icons">home</span>
                        <span>Accueil</span>
                    </a>
                    <a href="#" onclick="showTab('demandes')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">mail</span>
                        <span>Demandes</span>
                    </a>
                    <a href="#" onclick="showTab('taches')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">calendar_today</span>
                        <span>Tâches</span>
                    </a>

                    <a href="#" onclick="showTab('messagerie')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">message</span>
                        <span>Messagerie</span>
                        <?php if (isset($unreadMessages) && $unreadMessages > 0): ?>
                            <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                <?= $unreadMessages ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <a href="#" onclick="showTab('profil')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">person</span>
                        <span>Profil</span>
                    </a>

                    <a href="#" onclick="showTab('services')" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 font-medium menu-item">
                        <span class="material-icons">build</span>
                        <span>Mes Services</span>
                    </a>


                    <a href="../controllers/logout.php" class="flex items-center space-x-3 p-3 rounded-lg text-red-500 hover:bg-red-50 font-medium menu-item">
                        <span class="material-icons">logout</span>
                        <span>Déconnexion</span>
                    </a>
                </nav>

                <div class="mt-8 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                        <span>Statut du compte</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $prestataire['etat_compte'] === 'Validé' ? 'bg-green-100 text-green-800' : ($prestataire['etat_compte'] === 'En attente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= htmlspecialchars($prestataire['etat_compte']) ?>
                        </span>
                    </div>
                    <div class="text-sm text-gray-500 mb-1">
                        <span class="font-medium text-gray-700">Membre depuis:</span> 
                        <?= !empty($prestataire['date_inscription']) ? date('F Y', strtotime($prestataire['date_inscription'])) : 'Non renseignée' ?>
                    </div>
                    <div class="text-sm text-gray-500">
                        <span class="font-medium text-gray-700">Note moyenne:</span> 
                        <?= $stats['evaluation_moyenne'] ?>/5
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenu principal -->
        <div class="flex-1">
            <!-- Section Accueil -->
            <section id="accueil" class="tab-section mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Tableau de Bord</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-800">Demandes en attente</h3>
                            <p class="text-2xl font-bold text-blue-600"><?= count($demandes) ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-green-800">Tâches en cours</h3>
                            <p class="text-2xl font-bold text-green-600"><?= count($taches) ?></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-purple-800">Tâches terminées</h3>
                            <p class="text-2xl font-bold text-purple-600"><?= $stats['taches_completees'] ?></p>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg border border-gray-200 mb-8" style="height: 500px;">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>

                     <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Dernières tâches terminées</h3>
                    <?php if (!empty($taches_terminees)): ?>
                        <div class="space-y-4">
                            <?php foreach ($taches_terminees as $tache): ?>
                                <div class="border-b border-gray-200 pb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="font-medium"><?= htmlspecialchars($tache['service_nom']) ?></span>
                                            <p class="text-sm text-gray-600">Client: <?= htmlspecialchars($tache['client_nom']) ?></p>
                                        </div>
                                        <span class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($tache['date_heure_rdv'])) ?></span>
                                    </div>
                                    
                                    <?php if (!empty($tache['note'])): ?>
                                        <div class="mt-2">
                                            <div class="flex items-center">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="material-icons text-sm <?= $i <= $tache['note'] ? 'text-yellow-400' : 'text-gray-300' ?>">
                                                        <?= $i <= $tache['note'] ? 'star' : 'star_border' ?>
                                                    </span>
                                                <?php endfor; ?>
                                                <span class="ml-1 text-sm text-gray-600"><?= $tache['note'] ?>/5</span>
                                            </div>
                                            <?php if (!empty($tache['commentaire'])): ?>
                                                <p class="mt-1 text-sm text-gray-700 italic">
                                                    "<?= htmlspecialchars($tache['commentaire']) ?>"
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="mt-2 text-sm text-gray-500">En attente d'évaluation</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">Aucune tâche terminée récente</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Section Demandes -->
            <section id="demandes" class="tab-section active mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Demandes Reçues</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date & Heure</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Adresse</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($demandes as $demande): ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 font-medium"><?= htmlspecialchars($demande['client_nom']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <?= htmlspecialchars($demande['client_email']) ?><br>
                                        <?= htmlspecialchars($demande['telephone'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($demande['description']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-700"><?= date('Y-m-d H:i', strtotime($demande['date_heure_rdv'])) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($demande['lieu']) ?></td>
                                    <td class="px-4 py-2 text-sm">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                                            <input type="hidden" name="action" value="accepter">
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Accepter</button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                                            <input type="hidden" name="action" value="refuser">
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Refuser</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($demandes)): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-2 text-center text-sm text-gray-500">Aucune demande</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Section Tâches -->
            <section id="taches" class="tab-section mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Tâches en cours</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Validation</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($taches as $tache): ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 font-medium"><?= htmlspecialchars($tache['client_nom']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($tache['service_nom']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-700"><?= date('Y-m-d', strtotime($tache['date_heure_rdv'])) ?></td>
                                    <td class="px-4 py-2 text-sm">
                                        <form method="POST">
                                            <input type="hidden" name="demande_id" value="<?= $tache['id'] ?>">
                                            <input type="hidden" name="action" value="changer_statut">
                                            <select name="statut" onchange="this.form.submit()" class="border rounded px-2 py-1">
                                                <option value="Validée" <?= $tache['etat'] === 'Validée' ? 'selected' : '' ?>>En cours</option>
                                                <option value="Terminée" <?= $tache['etat'] === 'Terminée' ? 'selected' : '' ?>>Terminée</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <?= $tache['etat'] === 'Terminée' ? '<span class="text-green-600">Terminé</span>' : '<span class="text-blue-600">En cours</span>' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($taches)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500">Aucune tâche en cours</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>


            <!-- Section Messagerie -->
            <section id="messagerie" class="tab-section mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Messagerie</h2>
                    
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Liste des conversations -->
                        <div class="md:w-1/3 border-r border-gray-200 pr-4">
                            <div class="relative mb-4">
                                <input type="text" placeholder="Rechercher une conversation..." class="w-full p-2 border rounded-lg pl-10">
                                <span class="material-icons absolute left-3 top-2.5 text-gray-400">search</span>
                            </div>
                            
                            <div class="space-y-2 max-h-[600px] overflow-y-auto">
                                <?php if (!empty($conversations)): ?>
                                    <?php foreach ($conversations as $conv): ?>
                                        <div class="p-3 rounded-lg hover:bg-gray-50 cursor-pointer flex items-center conversation-item" 
                                            data-client-id="<?= $conv['client_id'] ?>">
                                            <img src="<?= getProfileImage($conv['client_prenom'] ?? '', $conv['client_nom'] ?? '', $conv['client_photo'] ?? null) ?>"
     alt="<?= htmlspecialchars(($conv['client_prenom'] ?? '') . ' ' . ($conv['client_nom'] ?? '')) ?>"
     class="w-10 h-10 rounded-full object-cover mr-3">

                                            <div class="flex-1">
                                                <h4 class="font-medium"><?= htmlspecialchars(($conv['client_prenom'] ?? '') . ' ' . ($conv['client_nom'] ?? '')) ?></h4>
                                                <p class="text-sm text-gray-500 truncate">Dernier message...</p>
                                            </div>
                                            <?php if (!empty($conv['last_message_date'])): ?>
                                                    <span class="text-xs text-gray-400"><?= date('d/m', strtotime($conv['last_message_date'])) ?></span>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-400">Aucun</span>
                                                <?php endif; ?>

                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-gray-500 p-3">Aucune conversation</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Zone de discussion -->
                        <div class="md:w-2/3">
                            <div id="chat-container" class="hidden">
                                <div class="flex items-center border-b border-gray-200 pb-3 mb-4">
                                    <img id="current-chat-photo" src="" alt="" class="w-10 h-10 rounded-full object-cover mr-3">
                                    <h3 id="current-chat-name" class="font-medium"></h3>
                                </div>
                                
                                <div id="messages-container" class="h-[400px] overflow-y-auto mb-4 space-y-3 p-2">
                                    <!-- Les messages seront chargés ici via AJAX -->
                                </div>
                                
                                <form id="message-form" class="flex gap-2">
                                    <input type="hidden" id="recipient-id" name="recipient_id">
                                    <input type="text" name="message" placeholder="Écrivez un message..." 
                                        class="flex-1 border rounded-lg p-2" required>
                                    <button type="submit" class="bg-blue-600 text-white p-2 rounded-lg">
                                        <span class="material-icons">send</span>
                                    </button>
                                </form>
                            </div>
                            
                            <div id="no-chat-selected" class="flex flex-col items-center justify-center h-[400px] text-gray-500">
                                <span class="material-icons text-4xl mb-2">forum</span>
                                <p>Sélectionnez une conversation pour commencer à discuter</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>



            <!-- Section Profil -->
            <section id="profil" class="tab-section mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Mon Profil</h2>
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="md:w-1/3">
                            <div class="bg-blue-50 rounded-lg p-4 flex flex-col items-center">
                                <img src="<?= $photo_profil ?>" alt="Photo profil" class="rounded-full w-32 h-32 object-cover border-4 border-white mb-4">
                                <h3 class="text-xl font-bold text-center"><?= htmlspecialchars($prestataire['prenom'] . ' ' . $prestataire['nom']) ?></h3>
                                
                                <?php if ($prestataire['type_prestataire'] === 'entreprise'): ?>
                                    <span class="mt-2 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                        Entreprise
                                    </span>
                                <?php else: ?>
                                    <span class="mt-2 bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                        Indépendant
                                    </span>
                                <?php endif; ?>
                                
                                <div class="mt-4 space-y-2 w-full">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="material-icons mr-2 text-blue-500">email</span>
                                        <span><?= htmlspecialchars($prestataire['email']) ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="material-icons mr-2 text-blue-500">phone</span>
                                        <span><?= htmlspecialchars($prestataire['telephone'] ?? 'Non renseigné') ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="material-icons mr-2 text-blue-500">location_on</span>
                                        <span><?= htmlspecialchars($prestataire['ville'] ?? '') ?>, <?= htmlspecialchars($prestataire['commune'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md:w-2/3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-sm text-gray-500">Note moyenne</p>
                                    <p class="font-bold text-gray-800"><?= $stats['evaluation_moyenne'] ?>/5</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-sm text-gray-500">Tâches complétées</p>
                                    <p class="font-bold text-gray-800"><?= $stats['taches_completees'] ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-sm text-gray-500">Membre depuis</p>
                                    <p class="font-bold text-gray-800">
                                        <?= !empty($prestataire['date_inscription']) ? date('F Y', strtotime($prestataire['date_inscription'])) : 'Non renseignée' ?>
                                    </p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-sm text-gray-500">Statut</p>
                                    <p class="font-bold <?= $prestataire['etat_compte'] === 'Validé' ? 'text-green-600' : 'text-yellow-600' ?>">
                                        <?= htmlspecialchars($prestataire['etat_compte']) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <form method="POST" action="../controllers/update_description.php" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                <h3 class="text-lg font-semibold mb-3">Description</h3>
                                <textarea name="description" class="w-full border rounded-lg p-3" rows="5" 
                                          placeholder="Décrivez vos compétences, expériences et spécialités..."><?= !empty($prestataire['description']) ? htmlspecialchars($prestataire['description']) : '' ?></textarea>
                                <button type="submit" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                    <?= !empty($prestataire['description']) ? 'Mettre à jour' : 'Enregistrer' ?>
                                </button>
                            </form>

                            <!-- Dans la section Profil, après la partie description -->
                                <div class="mt-8">
                                    <h3 class="text-lg font-semibold mb-4">Images du profil</h3>
                                    
                                    <!-- Formulaire d'upload -->
                        <form id="upload-image-form" enctype="multipart/form-data" class="mb-6">
                            <div class="flex items-center gap-4">
                                <input type="file" name="images[]" multiple accept="image/*" class="border p-2 rounded" required>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    Ajouter des images
                                </button>
                            </div>
                        </form>
                                    
                                    <!-- Galerie d'images -->
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        <?php
                                        // Récupérer les images du prestataire
                                        $stmt = $pdo->prepare("SELECT * FROM prestataire_images WHERE prestataire_id = ? ORDER BY upload_date DESC");
                                        $stmt->execute([$prestataire_id]);
                                        $images = $stmt->fetchAll();
                                        
                                        foreach ($images as $image): ?>
                                            <div class="relative group">
                                                <img src="../uploads/prestataires/<?= htmlspecialchars($image['image_path']) ?>" 
                                                    alt="Image profil prestataire" 
                                                    class="w-full h-40 object-cover rounded-lg shadow">
                                                <a href="../controllers/delete_prestataire_image.php?id=<?= $image['id'] ?>" 
                                                class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="material-icons text-sm">delete</span>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if (empty($images)): ?>
                                        <p class="text-gray-500">Aucune image ajoutée pour le moment</p>
                                    <?php endif; ?>
                                </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="services" class="tab-section mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Mes Services</h2>

        <!-- Formulaire d'ajout/modification -->
        <form method="POST" action="../controllers/service_save.php" class="space-y-4">
            <input type="hidden" name="service_id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nom du service</label>
                <input type="text" name="nom" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" required rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tarif</label>
                <input type="number" step="0.01" name="tarif" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Devise</label>
                
            <select name="devise" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="CDF">CDF</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Enregistrer</button>
                </form>

                <hr class="my-6">

                <!-- Liste des services existants -->
                <h3 class="text-xl font-semibold mb-2">Services existants</h3>
                <ul class="space-y-3">
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM services WHERE prestataire_id = ?");
                    $stmt->execute([$prestataire_id]);
                    $services = $stmt->fetchAll();
                    foreach ($services as $srv): ?>
                        <li class="border p-3 rounded-md flex justify-between items-center">
                            <div>
                                <strong><?= htmlspecialchars($srv['nom'] ?? '') ?></strong><br>
                                <small><?= htmlspecialchars($srv['description'] ?? '') ?></small><br>
                                <span class="text-sm text-gray-600">
                                    <?= htmlspecialchars($srv['tarif'] ?? '') ?> <?= htmlspecialchars($srv['devise'] ?? '') ?>
                                </span>
                            </div>
                            <a href="modifier_service.php?id=<?= htmlspecialchars($srv['id'] ?? '') ?>" class="text-blue-600 hover:underline">Modifier</a>
                        </li>

                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        </div>
    </main>

    <!-- Mobile bottom navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 z-50">
        <div class="flex justify-around">
            <a href="#" onclick="showTab('accueil')" class="flex flex-col items-center justify-center p-3 text-gray-500">
                <span class="material-icons">home</span>
                <span class="text-xs mt-1">Accueil</span>
            </a>
            <a href="#" onclick="showTab('demandes')" class="flex flex-col items-center justify-center p-3 text-blue-600">
                <span class="material-icons">mail</span>
                <span class="text-xs mt-1">Demandes</span>
            </a>
            <a href="#" onclick="showTab('taches')" class="flex flex-col items-center justify-center p-3 text-gray-500">
                <span class="material-icons">calendar_today</span>
                <span class="text-xs mt-1">Tâches</span>
            </a>
            <a href="#" onclick="showTab('profil')" class="flex flex-col items-center justify-center p-3 text-gray-500">
                <span class="material-icons">person</span>
                <span class="text-xs mt-1">Profil</span>
            </a>
        </div>
    </div>

    <script>
        function showTab(id) {
            // Hide all tabs
            document.querySelectorAll('.tab-section').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(id).classList.add('active');
            
            // Update active state in sidebar
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'text-blue-600');
                item.classList.add('text-gray-600', 'hover:bg-gray-50');
            });
            
            // Update active state in mobile nav
            document.querySelectorAll('[onclick^="showTab"]').forEach(item => {
                item.classList.remove('text-blue-600');
                item.classList.add('text-gray-500');
            });
            
            // Set active item
            const activeItem = document.querySelector(`[onclick="showTab('${id}')"]`);
            if (activeItem) {
                activeItem.classList.remove('text-gray-600', 'hover:bg-gray-50', 'text-gray-500');
                activeItem.classList.add('bg-blue-50', 'text-blue-600');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize chart
            const ctx = document.getElementById('statusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['En attente', 'En cours', 'Terminées'],
                    datasets: [{
                        data: [<?= count($demandes) ?>, <?= count($taches) ?>, <?= $stats['taches_completees'] ?>],
                        backgroundColor: ['#F59E0B', '#3B82F6', '#10B981'],
                        borderWidth: 0
                    }]
                },
                options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            font: {
                                size: 20
                            }
                        }
                    }
            },
            layout: {
                padding: 5 
            }
}


                
            });
            
            // Set demandes tab as active by default
            showTab('accueil');
        });

        // Dans la partie JavaScript du dashboard-prestataire.php
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des conversations
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', function() {
            const clientId = this.dataset.clientId;
            const clientName = this.querySelector('h4').textContent;
            const clientPhoto = this.querySelector('img').src;
            
            // Mettre à jour l'interface
            document.getElementById('current-chat-photo').src = clientPhoto;
            document.getElementById('current-chat-name').textContent = clientName;
            document.getElementById('recipient-id').value = clientId;
            document.getElementById('chat-container').classList.remove('hidden');
            document.getElementById('no-chat-selected').classList.add('hidden');
            
            // Charger les messages
            loadMessages(clientId);
        });
    });
    
    // Charger les messages d'une conversation
    function loadMessages(clientId) {
        fetch(`../controllers/get_messages.php?recipient_id=${clientId}`)
            .then(response => response.json())
            .then(messages => {
                const container = document.getElementById('messages-container');
                container.innerHTML = '';
                
                messages.forEach(msg => {
                    const isSender = msg.sender_id == <?= $_SESSION['user']['id'] ?>;
                    const messageClass = isSender ? 'bg-blue-100 ml-auto' : 'bg-gray-100 mr-auto';
                    
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `max-w-[70%] p-3 rounded-lg ${messageClass}`;
                    messageDiv.innerHTML = `
                        <p class="text-gray-800">${msg.contenu}</p>
                        <p class="text-xs text-gray-500 mt-1 text-right">
                            ${new Date(msg.date_envoi).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </p>
                    `;
                    
                    container.appendChild(messageDiv);
                });
                
                // Faire défiler vers le bas
                container.scrollTop = container.scrollHeight;
            });
    }
    
    // Envoyer un message
    document.getElementById('message-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('sender_id', <?= $_SESSION['user']['id'] ?>);
        
        fetch('../controllers/send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.reset();
                loadMessages(document.getElementById('recipient-id').value);
            }
        });
    });
    
    // Rafraîchir périodiquement les messages (toutes les 30 secondes)
    setInterval(() => {
        const recipientId = document.getElementById('recipient-id').value;
        if (recipientId) {
            loadMessages(recipientId);
        }
    }, 30000);
});
    </script>
</body>
</html>