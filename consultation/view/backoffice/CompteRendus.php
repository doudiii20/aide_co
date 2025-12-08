<?php
require_once __DIR__ . '/../../auth/config.php';
require_once __DIR__ . '/../../model/CompteRendu.php';
require_once __DIR__ . '/../../controller/CompteRenduController.php';
require_once __DIR__ . '/../../libs/EmailService.php';

$controller = new CompteRenduController($pdo);
$emailService = new EmailService();
$message = '';
$messageType = '';

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($controller->deleteCompteRendu($id)) {
        $message = "Compte rendu supprimé avec succès !";
        $messageType = 'success';
    } else {
        $message = "Erreur lors de la suppression.";
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_compte_rendu = $_POST['id_compte_rendu'] ?? null;
    $id_consultation = !empty($_POST['id_consultation']) ? $_POST['id_consultation'] : null;
    $sendEmail = isset($_POST['send_email']) && $_POST['send_email'] === '1';
    
    $compteRendu = new CompteRendu(
        $id_compte_rendu,
        $_POST['nom'],
        $_POST['email'],
        $_POST['description'],
        $_POST['statut'],
        $id_consultation
    );

    if ($id_compte_rendu) {
        // Modification
        if ($controller->updateCompteRendu($compteRendu)) {
            $message = "Compte rendu modifié avec succès !";
            $messageType = 'success';
            
            // Envoyer l'email si demandé
            if ($sendEmail) {
                $compteRenduData = $compteRendu->toArray();
                // Récupérer la consultation si elle existe
                if ($id_consultation) {
                    $consultation = $controller->getConsultationById($id_consultation);
                    if ($consultation && isset($consultation['date_consultation'])) {
                        $compteRenduData['date_consultation'] = $consultation['date_consultation'];
                    }
                }
                
                $emailSent = $emailService->sendCompteRenduConfirmation(
                    $_POST['email'],
                    $_POST['nom'],
                    $compteRenduData
                );
                
                if ($emailSent) {
                    // Vérifier si on est en localhost
                    $isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) || 
                                   strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
                    if ($isLocalhost) {
                        $message .= " ✅ Email sauvegardé pour test (localhost) 
                    <a href='../../test_emails/index.php' target='_blank' style='color: #4caf50; font-weight: 600; text-decoration: underline;'>Voir l'email</a>";
                    } else {
                        $message .= " ✅ Email de confirmation envoyé au patient.";
                    }
                } else {
                    $message .= " ⚠️ L'envoi de l'email a échoué. En localhost, l'email est sauvegardé dans test_emails/. En production, vérifiez la configuration SMTP.";
                }
            }
        } else {
            $message = "Erreur lors de la modification.";
            $messageType = 'error';
        }
    } else {
        // Ajout
        if ($controller->addCompteRendu($compteRendu)) {
            // Récupérer l'ID du compte rendu créé (le dernier inséré)
            $allComptesRendus = $controller->getAllComptesRendus();
            $newCompteRendu = null;
            if (!empty($allComptesRendus)) {
                // Trouver le compte rendu qui correspond (même email et nom)
                foreach ($allComptesRendus as $cr) {
                    if ($cr['email'] === $_POST['email'] && $cr['nom'] === $_POST['nom']) {
                        $newCompteRendu = $cr;
                        break;
                    }
                }
                // Si pas trouvé, prendre le premier (le plus récent)
                if (!$newCompteRendu) {
                    $newCompteRendu = $allComptesRendus[0];
                }
            }
            
            $message = "Compte rendu ajouté avec succès !";
            $messageType = 'success';
            
            // Envoyer l'email si demandé
            if ($sendEmail && $newCompteRendu) {
                $emailSent = $emailService->sendCompteRenduConfirmation(
                    $_POST['email'],
                    $_POST['nom'],
                    $newCompteRendu
                );
                
                if ($emailSent) {
                    // Vérifier si on est en localhost
                    $isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) || 
                                   strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
                    if ($isLocalhost) {
                        $message .= " ✅ Email sauvegardé pour test (localhost) - <a href='../../test_emails/index.php' target='_blank' style='color: #4caf50; font-weight: 600; text-decoration: underline;'>Voir l'email</a>";
                    } else {
                        $message .= " ✅ Email de confirmation envoyé au patient.";
                    }
                } else {
                    $message .= " ⚠️ L'envoi de l'email a échoué. En localhost, l'email est sauvegardé dans test_emails/. En production, vérifiez la configuration SMTP.";
                }
            }
        } else {
            $message = "Erreur lors de l'ajout.";
            $messageType = 'error';
        }
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $editDataObj = $controller->getCompteRenduById($_GET['edit']);
    if ($editDataObj) {
        $editData = $editDataObj->toArray();
    }
}

// Si une consultation est passée en paramètre, pré-remplir le formulaire
$consultationId = $_GET['consultation'] ?? null;
if ($consultationId && !$editData) {
    $consultation = $controller->getConsultationById($consultationId);
    if ($consultation) {
        // Pré-remplir avec les données de la consultation si disponibles
        $editData = [
            'id_consultation' => $consultationId,
            'nom' => '',
            'email' => '',
            'description' => '',
            'statut' => ''
        ];
    }
}

// Récupération des filtres de recherche
$searchStatut = $_GET['search_statut'] ?? '';
$searchDate = $_GET['search_date'] ?? '';
$searchNom = $_GET['search_nom'] ?? '';

// Récupérer tous les comptes rendus
$allComptesRendus = $controller->getAllComptesRendus();

// Appliquer les filtres
$comptesRendus = $allComptesRendus;
if ($searchStatut || $searchDate || $searchNom) {
    $comptesRendus = array_filter($allComptesRendus, function($cr) use ($searchStatut, $searchDate, $searchNom) {
        // Filtre par statut
        if ($searchStatut && $cr['statut'] !== $searchStatut) {
            return false;
        }
        
        // Filtre par date (recherche dans date_consultation liée)
        if ($searchDate) {
            $crDate = null;
            // Chercher la date de consultation liée
            if (isset($cr['date_consultation']) && !empty($cr['date_consultation'])) {
                $crDate = date('Y-m-d', strtotime($cr['date_consultation']));
            }
            // Si pas de date de consultation, on garde le résultat (pas de filtre)
            // Si une date existe mais ne correspond pas, on exclut
            if ($crDate !== null && $crDate !== $searchDate) {
                return false;
            }
            // Si on cherche une date spécifique mais qu'il n'y a pas de date de consultation, on exclut
            if ($crDate === null) {
                return false;
            }
        }
        
        // Filtre par nom
        if ($searchNom) {
            $nomMatch = stripos($cr['nom'], $searchNom) !== false;
            $emailMatch = stripos($cr['email'], $searchNom) !== false;
            $descMatch = stripos($cr['description'], $searchNom) !== false;
            if (!$nomMatch && !$emailMatch && !$descMatch) {
                return false;
            }
        }
        
        return true;
    });
    $comptesRendus = array_values($comptesRendus); // Réindexer le tableau
}

$consultations = $controller->getAllConsultations();
$hasConsultationColumn = $controller->consultationColumnExists();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Comptes Rendus | Supportini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #d32f2f;
            --dark-red: #b71c1c;
            --light-red: #ff6659;
            --dark-bg: #121212;
            --card-bg: #1e1e1e;
            --text-light: #f5f5f5;
            --text-muted: #aaaaaa;
            --border-color: #333333;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: var(--card-bg);
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
            z-index: 100;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-header {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        .sidebar-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .sidebar-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-light);
        }
        .sidebar-title span {
            color: var(--primary-red);
        }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
        }
        .sidebar-link {
            padding: 15px 25px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
        }
        .sidebar-link.active {
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--primary-red);
            border-left-color: var(--primary-red);
        }
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-light);
        }
        .page-title i {
            color: var(--primary-red);
            margin-right: 10px;
        }
        /* Message */
        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .msg-error {
            background-color: rgba(211, 47, 47, 0.1);
            color: #ff6659;
            border: 1px solid rgba(211, 47, 47, 0.3);
        }
        .msg-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        /* Table */
        .table-container {
            background-color: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            margin-bottom: 40px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background-color: var(--primary-red);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
        }
        .table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-light);
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        .table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-primary {
            background-color: var(--primary-red);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--dark-red);
        }
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-light);
        }
        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .btn-danger {
            background-color: transparent;
            border: 1px solid #d32f2f;
            color: #d32f2f;
        }
        .btn-danger:hover {
            background-color: rgba(211, 47, 47, 0.1);
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        /* Form */
        .form-container {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .form-title {
            font-size: 22px;
            margin-bottom: 25px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-light);
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-red);
        }
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        .error-message {
            color: #ff6659;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
        .form-control.error {
            border-color: #ff6659;
        }
        .text-muted {
            color: var(--text-muted);
        }
        /* Style similaire pour le select Statut */
#statut {
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.05) !important;
}
#statut option {
    color: #ffffff;
    background-color: #1e1e1e;
}

    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../uploads/logoN.png" class="sidebar-logo" alt="Logo">
            <h2 class="sidebar-title">SUPPORTINI<span>.TN</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-table-cells-large"></i> Dashboard
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-user"></i> Utilisateurs
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Forum
            </a>
            <a href="consultations.php" class="sidebar-link">
                <i class="fa-solid fa-calendar-check"></i> Rendez-vous
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-calendar"></i> Événements
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Catégories
            </a>
            <a href="CompteRendus.php" class="sidebar-link active">
                <i class="fa-solid fa-file-alt"></i> Comptes Rendus
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-exclamation-circle"></i> Réclamation
            </a>
            <a href="/logout.php" class="sidebar-link">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </a>
            <a href="./dashboard.php" class="sidebar-link">
    <i class="fa-solid fa-chart-simple"></i> Dashboard
</a>


        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-file-alt"></i> Gestion des Comptes Rendus</h1>
        </div>
 



        <?php if($message): ?>
            <div class="message <?= $messageType === 'success' ? 'msg-success' : 'msg-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if($consultationId && !$editData && $hasConsultationColumn): ?>
            <?php 
            $consultationInfo = $controller->getConsultationById($consultationId);
            if ($consultationInfo): 
            ?>
                <div class="message" style="background-color: rgba(33, 150, 243, 0.1); color: #42a5f5; border: 1px solid rgba(33, 150, 243, 0.3); padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <i class="fa-solid fa-info-circle"></i> 
                    <strong>Création d'un compte rendu pour la consultation #<?= htmlspecialchars($consultationId) ?></strong>
                    <?php if(isset($consultationInfo['date_consultation'])): ?>
                        - Date: <?= date('d/m/Y', strtotime($consultationInfo['date_consultation'])) ?>
                    <?php endif; ?>
                    <a href="consultations.php?view=<?= $consultationId ?>" style="color: #42a5f5; margin-left: 10px; text-decoration: underline;">
                        <i class="fa-solid fa-arrow-left"></i> Retour à la consultation
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Add/Edit Form -->
        <div class="form-container">
            <h2 class="form-title">
                <i class="fa-solid <?= $editData ? 'fa-pen' : 'fa-plus' ?>"></i>
                <span><?= $editData ? "Modifier Compte Rendu" : "Ajouter Compte Rendu" ?></span>
            </h2>
            <form method="POST" id="data-form" novalidate>
                <input type="hidden" name="id_compte_rendu" id="id_compte_rendu" value="<?= $editData['id_compte_rendu'] ?? '' ?>">

                <?php if($hasConsultationColumn): ?>
                <div class="form-group">
                    <label class="form-label" for="id_consultation">Consultation</label>
                    <select name="id_consultation" id="id_consultation" class="form-control">
                        <option value="">-- Aucune consultation --</option>
                        <?php foreach($consultations as $consultation): ?>
                            <option value="<?= $consultation['id_consultation'] ?>" 
                                <?= (isset($editData['id_consultation']) && $editData['id_consultation'] == $consultation['id_consultation']) ? 'selected' : '' ?>>
                                Consultation #<?= $consultation['id_consultation'] ?> 
                                <?php if(isset($consultation['date_consultation'])): ?>
                                    - <?= date('d/m/Y', strtotime($consultation['date_consultation'])) ?>
                                <?php endif; ?>
                                <?php if(isset($consultation['motif_consultation'])): ?>
                                    - <?= htmlspecialchars(substr($consultation['motif_consultation'], 0, 30)) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-message" id="error_id_consultation"></span>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="nom">Nom *</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="<?= htmlspecialchars($editData['nom'] ?? '') ?>">
                    <span class="error-message" id="error_nom"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input type="text" name="email" id="email" class="form-control" value="<?= htmlspecialchars($editData['email'] ?? '') ?>">
                    <span class="error-message" id="error_email"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description / Ordonnance *</label>
                    <textarea name="description" id="description" class="form-control" rows="8"><?= htmlspecialchars($editData['description'] ?? '') ?></textarea>
                    <span class="error-message" id="error_description"></span>
                    <button type="button" class="btn btn-outline" style="margin-top: 10px;" onclick="genererDescription()">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Générer description automatique
                    </button>
                </div>

                <div class="form-group">
                    <label class="form-label" for="statut">Statut *</label>
                    <select name="statut" id="statut" class="form-control">
                        <option value="">-- Choisir --</option>
                        <option value="rendezVous_termine" <?= (isset($editData['statut']) && $editData['statut']=='rendezVous_termine') ? 'selected' : '' ?>>Rendez-vous terminé</option>
                        <option value="autre_rendezVous_necessaire" <?= (isset($editData['statut']) && $editData['statut']=='autre_rendezVous_necessaire') ? 'selected' : '' ?>>Autre rendez-vous nécessaire</option>
                        <option value="suivi_recommande" <?= (isset($editData['statut']) && $editData['statut']=='suivi_recommande') ? 'selected' : '' ?>>Suivi recommandé</option>
                    </select>
                    <span class="error-message" id="error_statut"></span>
                </div>

                <!-- Option d'envoi d'email -->
                <div class="form-group" style="background-color: rgba(211, 47, 47, 0.1); padding: 20px; border-radius: 8px; border-left: 4px solid var(--primary-red); margin: 20px 0;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-light);">
                        <input type="checkbox" name="send_email" id="send_email" value="1" style="width: 20px; height: 20px; cursor: pointer;">
                        <span style="font-weight: 600;">
                            <i class="fa-solid fa-envelope" style="color: var(--primary-red);"></i>
                            Envoyer un email de confirmation au patient
                        </span>
                    </label>
                    <p style="margin-top: 10px; color: var(--text-muted); font-size: 13px; margin-left: 30px;">
                        <i class="fa-solid fa-info-circle"></i> 
                        Un email avec le compte rendu sera envoyé à l'adresse email du patient.
                    </p>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid <?= $editData ? 'fa-check' : 'fa-plus' ?>"></i>
                        <?= $editData ? "Mettre à jour" : "Ajouter" ?>
                    </button>
                    <?php if($editData): ?>
                        <a href="CompteRendus.php" class="btn btn-outline">
                            <i class="fa-solid fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Search/Filter Form -->
        <div class="form-container" style="margin-bottom: 30px;">
            <h2 class="form-title">
                <i class="fa-solid fa-filter"></i>
                <span>Rechercher et Filtrer</span>
            </h2>
            <form method="GET" class="search-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <input type="hidden" name="edit" value="<?= $_GET['edit'] ?? '' ?>">
                <input type="hidden" name="consultation" value="<?= $_GET['consultation'] ?? '' ?>">
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="search_nom">Rechercher (Nom, Email, Description)</label>
                    <input type="text" 
                           id="search_nom" 
                           name="search_nom" 
                           class="form-control" 
                           placeholder="Rechercher..." 
                           value="<?= htmlspecialchars($searchNom) ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="search_statut">Filtrer par Statut</label>
                    <select id="search_statut" name="search_statut" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="rendezVous_termine" <?= $searchStatut === 'rendezVous_termine' ? 'selected' : '' ?>>Rendez-vous terminé</option>
                        <option value="autre_rendezVous_necessaire" <?= $searchStatut === 'autre_rendezVous_necessaire' ? 'selected' : '' ?>>Autre rendez-vous nécessaire</option>
                        <option value="suivi_recommande" <?= $searchStatut === 'suivi_recommande' ? 'selected' : '' ?>>Suivi recommandé</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="search_date">Filtrer par Date (Consultation)</label>
                    <input type="date" 
                           id="search_date" 
                           name="search_date" 
                           class="form-control" 
                           value="<?= htmlspecialchars($searchDate) ?>">
                </div>

                <div class="form-actions" style="display: flex; gap: 10px; margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fa-solid fa-magnifying-glass"></i> Rechercher
                    </button>
                    <a href="CompteRendus.php" class="btn btn-outline" style="flex: 1;">
                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                    </a>
                </div>
            </form>
            
            <?php if($searchStatut || $searchDate || $searchNom): ?>
                <div style="margin-top: 15px; padding: 10px; background-color: rgba(211, 47, 47, 0.1); border-radius: 4px; color: var(--text-light);">
                    <i class="fa-solid fa-info-circle"></i> 
                    <strong>Filtres actifs :</strong>
                    <?php if($searchNom): ?>
                        <span style="margin-left: 10px;">Recherche: "<?= htmlspecialchars($searchNom) ?>"</span>
                    <?php endif; ?>
                    <?php if($searchStatut): ?>
                        <span style="margin-left: 10px;">Statut: <?= htmlspecialchars($searchStatut) ?></span>
                    <?php endif; ?>
                    <?php if($searchDate): ?>
                        <span style="margin-left: 10px;">Date: <?= date('d/m/Y', strtotime($searchDate)) ?></span>
                    <?php endif; ?>
                    <span style="margin-left: 15px; color: var(--primary-red);">
                        (<?= count($comptesRendus) ?> résultat(s) sur <?= count($allComptesRendus) ?>)
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if($hasConsultationColumn): ?>
                        <th>Consultation</th>
                        <?php endif; ?>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($comptesRendus)): ?>
                        <tr>
                            <td colspan="<?= $hasConsultationColumn ? '7' : '6' ?>" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                <?php if($searchStatut || $searchDate || $searchNom): ?>
                                    <i class="fa-solid fa-search" style="font-size: 32px; margin-bottom: 10px; display: block; color: var(--border-color);"></i>
                                    <strong>Aucun résultat trouvé</strong><br>
                                    <span style="font-size: 14px;">Aucun compte rendu ne correspond à vos critères de recherche.</span><br>
                                    <a href="CompteRendus.php" style="color: var(--primary-red); text-decoration: underline; margin-top: 10px; display: inline-block;">
                                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser les filtres
                                    </a>
                                <?php else: ?>
                                    <i class="fa-solid fa-file-circle-question" style="font-size: 32px; margin-bottom: 10px; display: block; color: var(--border-color);"></i>
                                    <strong>Aucun compte rendu disponible</strong>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($comptesRendus as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['id_compte_rendu']) ?></td>
                                <?php if($hasConsultationColumn): ?>
                                <td>
                                    <?php if(!empty($c['id_consultation']) || !empty($c['consultation_id'])): ?>
                                        <a href="consultations.php?view=<?= $c['id_consultation'] ?? $c['consultation_id'] ?>" 
                                           style="color: var(--primary-red); text-decoration: none;">
                                            <i class="fa-solid fa-calendar-check"></i> 
                                            Consultation #<?= $c['id_consultation'] ?? $c['consultation_id'] ?>
                                        </a>
                                        <?php if(isset($c['date_consultation'])): ?>
                                            <br><small style="color: var(--text-muted);">
                                                <?= date('d/m/Y', strtotime($c['date_consultation'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Aucune</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($c['nom']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= htmlspecialchars(substr($c['description'], 0, 50)) ?><?= strlen($c['description']) > 50 ? '...' : '' ?></td>
                                <td><?= htmlspecialchars($c['statut']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?= $c['id_compte_rendu'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-pen"></i> Modifier
                                        </a>
                                        <a href="?delete=<?= $c['id_compte_rendu'] ?>" 
                                           onclick="return confirm('Supprimer ce compte rendu ?')" 
                                           class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('data-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Réinitialiser les erreurs
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                msg.textContent = '';
            });
            
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('error');
            });
            
            let isValid = true;
            
            // Validation nom
            const nom = document.getElementById('nom').value.trim();
            if (nom === '') {
                showError('nom', 'Le nom est requis');
                isValid = false;
            }
            
            // Validation email
            const email = document.getElementById('email').value.trim();
            if (email === '') {
                showError('email', 'L\'email est requis');
                isValid = false;
            } else if (!validateEmail(email)) {
                showError('email', 'Veuillez entrer un email valide');
                isValid = false;
            }
            
            // Validation description
            const description = document.getElementById('description').value.trim();
            if (description === '') {
                showError('description', 'La description est requise');
                isValid = false;
            }
            
            // Validation statut
            const statut = document.getElementById('statut').value;
            if (statut === '') {
                showError('statut', 'Le statut est requis');
                isValid = false;
            }
            
            if (isValid) {
                this.submit();
            }
        });

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById('error_' + fieldId);
            field.classList.add('error');
            errorElement.textContent = message;
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function genererDescription() {
            const nom = document.getElementById('nom').value || '[Nom du patient]';
            const statut = document.getElementById('statut').value;
            const date = new Date().toLocaleDateString('fr-FR');
            
            let description = '';
            
            // Templates selon le statut
            const templates = {
                'rendezVous_termine': `COMPTE RENDU DE CONSULTATION
═══════════════════════════════════════

Patient: ${nom}
Date de consultation: ${date}

MOTIF DE CONSULTATION:
- Consultation de suivi

EXAMEN CLINIQUE:
- État général satisfaisant
- Paramètres vitaux dans les normes

DIAGNOSTIC:
- À compléter

CONCLUSION:
Le rendez-vous s'est déroulé normalement. Aucun suivi particulier n'est nécessaire.

ORDONNANCE:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. [Médicament 1] - [Posologie]
2. [Médicament 2] - [Posologie]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Signature du patient`,

                'autre_rendezVous_necessaire': `COMPTE RENDU DE CONSULTATION
═══════════════════════════════════════

Patient: ${nom}
Date de consultation: ${date}

MOTIF DE CONSULTATION:
- À compléter

EXAMEN CLINIQUE:
- État général à surveiller
- Examens complémentaires recommandés

DIAGNOSTIC:
- En cours d'évaluation

CONCLUSION:
Un autre rendez-vous est nécessaire pour compléter l'évaluation.

PROCHAIN RENDEZ-VOUS:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Date prévue: À définir
Motif: Suivi et réévaluation
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ORDONNANCE PROVISOIRE:
1. [Médicament] - [Posologie]

Signature du patient`,

                'suivi_recommande': `COMPTE RENDU DE CONSULTATION
═══════════════════════════════════════

Patient: ${nom}
Date de consultation: ${date}

MOTIF DE CONSULTATION:
- Consultation initiale / de suivi

EXAMEN CLINIQUE:
- À détailler

DIAGNOSTIC:
- Pathologie nécessitant un suivi régulier

PLAN DE SUIVI RECOMMANDÉ:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
□ Contrôle dans 1 mois
□ Analyses biologiques à réaliser
□ Examens complémentaires si nécessaire
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ORDONNANCE:
1. [Traitement de fond] - [Posologie]
2. [Traitement symptomatique] - [Posologie]

CONSEILS:
- Hygiène de vie adaptée
- Alimentation équilibrée
- Activité physique régulière

Signature du patient`
            };

            // Sélectionner le template
            if (statut && templates[statut]) {
                description = templates[statut];
            } else {
                description = `COMPTE RENDU DE CONSULTATION
═══════════════════════════════════════

Patient: ${nom}
Date: ${date}

MOTIF DE CONSULTATION:
- À compléter

EXAMEN CLINIQUE:
- À compléter

DIAGNOSTIC:
- À compléter

ORDONNANCE:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. [Médicament] - [Posologie]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Signature du patient`;
            }

            document.getElementById('description').value = description;
        }
    </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Données
    const totalCount = <?= count($allComptesRendus) ?>;
    const rdvTermineCount = <?= count(array_filter($allComptesRendus, fn($cr) => $cr['statut'] === 'rendezVous_termine')) ?>;
    const suiviRecoCount = <?= count(array_filter($allComptesRendus, fn($cr) => $cr['statut'] === 'suivi_recommande')) ?>;

    // Fonction pour créer un doughnut
    function createDoughnut(canvasId, value, color) {
        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                labels: ['Value', 'Rest'],
                datasets: [{
                    data: [value, 100 - value],
                    backgroundColor: [color, '#333333'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    // Calcul pour pourcentage (si nécessaire)
    const total = totalCount || 1; // éviter division par zéro
    createDoughnut('totalCR', 100, '#d32f2f');
    createDoughnut('rdvTermine', (rdvTermineCount/total)*100, '#4caf50');
    createDoughnut('suiviReco', (suiviRecoCount/total)*100, '#ffb300');
</script>


</body>
</html>
