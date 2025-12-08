<?php
require_once __DIR__ . '/../../auth/config.php';
require_once __DIR__ . '/../../model/Consultation.php';
require_once __DIR__ . '/../../controller/ConsultationController.php';

$controller = new RendezVousController($pdo);
$message = '';
$messageType = '';

// ----------------- SUPPRESSION -----------------
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($controller->deleteRendezVous($id)) {
        $message = "Rendez-vous supprimé avec succès !";
        $messageType = 'success';
    } else {
        $message = "Erreur lors de la suppression.";
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rendezVous = $_POST['id_rendezVous'] ?? null;
    $rendezVous = new RendezVous(
        $id_rendezVous,
        $_POST['nom_complet'],
        $_POST['email_universitaire'],
        $_POST['telephone'],
        $_POST['type_rendezVous'],
        $_POST['date_souhaitee'],
        $_POST['heure'],
        $_POST['duree'],
        $_POST['description']
    );

    if ($id_rendezVous) {
        // Modification
        if ($controller->updateRendezVous($rendezVous)) {
            $message = "Rendez-vous modifié avec succès !";
            $messageType = 'success';
        } else {
            $message = "Erreur lors de la modification.";
            $messageType = 'error';
        }
    } else {
        // Ajout
        if ($controller->addRendezVous($rendezVous)) {
            $message = "Rendez-vous ajouté avec succès !";
            $messageType = 'success';
        } else {
            $message = "Erreur lors de l'ajout.";
            $messageType = 'error';
        }
    }
}

// ----------------- EDIT -----------------
$editData = null;
if (isset($_GET['edit'])) {
    $editData = $controller->getRendezVousById($_GET['edit']);
    if ($editData) {
        $editData = $editData->toArray();
    }
}

// ----------------- LISTE -----------------
$rendezVous = $controller->getAllRendezVous();
$totalRendezVous = count($rendezVous);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Rendez-vous | Supportini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <style>
        :root {
            --primary-color: #d32f2f;
            --dark-primary: #b71c1c;
            --light-primary: #ff6659;
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
            min-height: 100vh;
        }
        /* Header */
        .main-header {
            background: linear-gradient(135deg, var(--dark-primary), var(--primary-color));
            padding: 25px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        .site-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }
        .site-title span {
            color: #ffccbc;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-title {
            font-size: 36px;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        .page-title i {
            color: var(--primary-color);
            margin-right: 15px;
        }
        .page-subtitle {
            color: var(--text-muted);
            font-size: 18px;
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
        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--card-bg), #252525);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-left: 4px solid var(--primary-color);
        }
        .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-light);
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }
        /* Form Container */
        .form-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 40px;
        }
        .form-title {
            font-size: 22px;
            margin-bottom: 25px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 12px;
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
            border-radius: 6px;
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.2);
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
        /* Style spécifique pour le select de durée */
        #duree {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        #duree option {
            color: #ffffff;
            background-color: #1e1e1e;
        }
        .date-picker, .time-picker {
            cursor: pointer;
            position: relative;
        }
        .form-group {
            position: relative;
        }
        .form-label i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        /* Flatpickr dark theme customization */
        .flatpickr-calendar {
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5) !important;
        }
        .flatpickr-months {
            background: var(--primary-color) !important;
        }
        .flatpickr-current-month {
            color: white !important;
        }
        .flatpickr-weekdays {
            background: rgba(211, 47, 47, 0.1) !important;
        }
        .flatpickr-weekday {
            color: var(--text-light) !important;
        }
        .flatpickr-day {
            color: var(--text-light) !important;
        }
        .flatpickr-day:hover {
            background: rgba(211, 47, 47, 0.2) !important;
            border-color: var(--primary-color) !important;
        }
        .flatpickr-day.selected {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .flatpickr-day.today {
            border-color: var(--primary-color) !important;
        }
        .flatpickr-time input {
            color: var(--text-light) !important;
            background: var(--card-bg) !important;
        }
        .flatpickr-time .flatpickr-time-separator {
            color: var(--text-light) !important;
        }
        /* Items Grid */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        .item-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        .item-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            border-color: var(--primary-color);
        }
        .item-content {
            padding: 25px;
        }
        .item-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-light);
            line-height: 1.3;
        }
        .item-meta {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        .item-meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 14px;
        }
        .item-meta-item i {
            color: var(--primary-color);
            width: 16px;
            font-size: 16px;
        }
        .item-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--dark-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.4);
        }
        .btn-danger {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        .btn-danger:hover {
            background-color: var(--primary-color);
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            grid-column: 1 / -1;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--border-color);
        }
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--text-light);
        }
        /* Footer */
        .main-footer {
            background-color: var(--card-bg);
            padding: 30px 0;
            margin-top: 60px;
            border-top: 1px solid var(--border-color);
        }
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        .footer-text {
            color: var(--text-muted);
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            .items-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="../../uploads/logoN.png" class="logo" alt="Supportini Logo">
                <h1 class="site-title">SUPPORTINI<span>.TN</span></h1>
            </div>
            <nav class="nav-links">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-house"></i> Accueil
                </a>
                <a href="consultations.php" class="nav-link active">
                    <i class="fa-solid fa-calendar-check"></i> Rendez-vous
                </a>
                <a href="comptes_rendus.php" class="nav-link">
                    <i class="fa-solid fa-file-alt"></i> Comptes Rendus
                </a>
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-user"></i> Mon Compte
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-calendar-check"></i> Gestion des Rendez-vous</h1>
            <p class="page-subtitle">Prenez rendez-vous pour une consultation</p>
        </div>

        <?php if($message): ?>
            <div class="message <?= $messageType === 'success' ? 'msg-success' : 'msg-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-value"><?= $totalRendezVous ?></div>
                <div class="stat-label">Rendez-vous total</div>
            </div>
        </div>

        <!-- Add/Edit Form -->
        <div class="form-container">
            <h2 class="form-title">
                <i class="fa-solid <?= $editData ? 'fa-pen' : 'fa-plus' ?>"></i>
                <span><?= $editData ? "Modifier Rendez-vous" : "Ajouter un Rendez-vous" ?></span>
            </h2>
            <form method="POST" id="data-form" novalidate>
                <input type="hidden" name="id_rendezVous" id="id_rendezVous" value="<?= $editData['id_rendezVous'] ?? '' ?>">

                <div class="form-group">
                    <label class="form-label" for="nom_complet">Nom complet *</label>
                    <input type="text" name="nom_complet" id="nom_complet" class="form-control" value="<?= htmlspecialchars($editData['nom_complet'] ?? '') ?>">
                    <span class="error-message" id="error_nom_complet"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email_universitaire">Email universitaire *</label>
                    <input type="text" name="email_universitaire" id="email_universitaire" class="form-control" value="<?= htmlspecialchars($editData['email_universitaire'] ?? '') ?>">
                    <span class="error-message" id="error_email_universitaire"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="telephone">Téléphone *</label>
                    <input type="text" name="telephone" id="telephone" class="form-control" value="<?= htmlspecialchars($editData['telephone'] ?? '') ?>">
                    <span class="error-message" id="error_telephone"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="type_rendezVous">Type de rendez-vous *</label>
                    <select name="type_rendezVous" id="type_rendezVous" class="form-control">
                        <option value="">Choisir</option>
                        <option value="individuelle" <?= (isset($editData['type_rendezVous']) && $editData['type_rendezVous']=='individuelle') ? 'selected' : '' ?>>Individuelle</option>
                        <option value="orientation" <?= (isset($editData['type_rendezVous']) && $editData['type_rendezVous']=='orientation') ? 'selected' : '' ?>>Orientation</option>
                        <option value="intervention_crise" <?= (isset($editData['type_rendezVous']) && $editData['type_rendezVous']=='intervention_crise') ? 'selected' : '' ?>>Intervention de crise</option>
                        <option value="soutien" <?= (isset($editData['type_rendezVous']) && $editData['type_rendezVous']=='soutien') ? 'selected' : '' ?>>Soutien</option>
                    </select>
                    <span class="error-message" id="error_type_rendezVous"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="date_souhaitee">
                        <i class="fa-solid fa-calendar"></i> Date souhaitée *
                    </label>
                    <input type="text" name="date_souhaitee" id="date_souhaitee" class="form-control date-picker" placeholder="Cliquez pour choisir une date" value="<?= htmlspecialchars($editData['date_souhaitee'] ?? '') ?>" readonly>
                    <span class="error-message" id="error_date_souhaitee"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="heure">
                        <i class="fa-solid fa-clock"></i> Heure *
                    </label>
                    <input type="text" name="heure" id="heure" class="form-control time-picker" placeholder="Cliquez pour choisir une heure" value="<?= htmlspecialchars($editData['heure'] ?? '') ?>" readonly>
                    <span class="error-message" id="error_heure"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="duree">Durée *</label>
                    <select name="duree" id="duree" class="form-control">
                        <option value="">-- Choisir --</option>
                        <option value="30min" <?= (isset($editData['duree']) && $editData['duree']=='30min') ? 'selected' : '' ?>>30 min</option>
                        <option value="1h" <?= (isset($editData['duree']) && $editData['duree']=='1h') ? 'selected' : '' ?>>1h</option>
                        <option value="2h" <?= (isset($editData['duree']) && $editData['duree']=='2h') ? 'selected' : '' ?>>2h</option>
                    </select>
                    <span class="error-message" id="error_duree"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description de votre situation *</label>
                    <textarea name="description" id="description" class="form-control" rows="5"><?= htmlspecialchars($editData['description'] ?? '') ?></textarea>
                    <span class="error-message" id="error_description"></span>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid <?= $editData ? 'fa-check' : 'fa-plus' ?>"></i>
                        <?= $editData ? "Mettre à jour" : "Ajouter" ?>
                    </button>
                    <?php if($editData): ?>
                        <a href="consultations.php" class="btn btn-danger">
                            <i class="fa-solid fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Items Grid -->
        <div class="items-grid">
            <?php if(empty($rendezVous)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <h3>Aucun rendez-vous</h3>
                    <p>Vous n'avez pas encore de rendez-vous. Ajoutez-en un en utilisant le formulaire ci-dessus.</p>
                </div>
            <?php else: ?>
                <?php foreach($rendezVous as $r): ?>
                    <div class="item-card">
                        <div class="item-content">
                            <h3 class="item-title"><?= htmlspecialchars($r['nom_complet']) ?></h3>
                            <div class="item-meta">
                                <div class="item-meta-item">
                                    <i class="fa-solid fa-envelope"></i>
                                    <span><?= htmlspecialchars($r['email_universitaire']) ?></span>
                                </div>
                                <div class="item-meta-item">
                                    <i class="fa-solid fa-phone"></i>
                                    <span><?= htmlspecialchars($r['telephone']) ?></span>
                                </div>
                                <div class="item-meta-item">
                                    <i class="fa-solid fa-calendar"></i>
                                    <span><?= htmlspecialchars($r['date_souhaitee']) ?></span>
                                </div>
                                <div class="item-meta-item">
                                    <i class="fa-solid fa-clock"></i>
                                    <span><?= htmlspecialchars($r['heure']) ?> - <?= htmlspecialchars($r['duree']) ?></span>
                                </div>
                                <div class="item-meta-item">
                                    <i class="fa-solid fa-tag"></i>
                                    <span><?= htmlspecialchars($r['type_rendezVous']) ?></span>
                                </div>
                            </div>
                            <div class="item-actions">
                                <a href="?edit=<?= $r['id_rendezVous'] ?>" class="btn btn-primary">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>
                                <a href="?delete=<?= $r['id_rendezVous'] ?>" onclick="return confirm('Supprimer ce rendez-vous ?')" class="btn btn-danger">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2024 Supportini - Tous droits réservés</p>
        </div>
    </footer>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script>
        // Initialiser Flatpickr pour la date
        flatpickr("#date_souhaitee", {
            dateFormat: "Y-m-d",
            locale: "fr",
            minDate: "today",
            enableTime: false,
            theme: "dark",
            allowInput: false,
            clickOpens: true,
            animate: true,
            monthSelectorType: "static"
        });

        // Initialiser Flatpickr pour l'heure
        flatpickr("#heure", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            locale: "fr",
            theme: "dark",
            allowInput: false,
            clickOpens: true,
            minuteIncrement: 15,
            defaultHour: 9,
            defaultMinute: 0
        });

        document.getElementById('data-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                msg.textContent = '';
            });
            
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('error');
            });
            
            let isValid = true;
            
            const nomComplet = document.getElementById('nom_complet').value.trim();
            if (nomComplet === '') {
                showError('nom_complet', 'Le nom complet est requis');
                isValid = false;
            }
            
            const email = document.getElementById('email_universitaire').value.trim();
            if (email === '') {
                showError('email_universitaire', 'L\'email universitaire est requis');
                isValid = false;
            } else if (!validateEmail(email)) {
                showError('email_universitaire', 'Veuillez entrer un email valide');
                isValid = false;
            }
            
            const telephone = document.getElementById('telephone').value.trim();
            if (telephone === '') {
                showError('telephone', 'Le téléphone est requis');
                isValid = false;
            } else if (!validatePhone(telephone)) {
                showError('telephone', 'Veuillez entrer un numéro de téléphone valide');
                isValid = false;
            }
            
            const typeRendezVous = document.getElementById('type_rendezVous').value;
            if (typeRendezVous === '') {
                showError('type_rendezVous', 'Le type de rendez-vous est requis');
                isValid = false;
            }
            
            const dateSouhaitee = document.getElementById('date_souhaitee').value.trim();
            if (dateSouhaitee === '') {
                showError('date_souhaitee', 'La date souhaitée est requise');
                isValid = false;
            } else if (!validateDate(dateSouhaitee)) {
                showError('date_souhaitee', 'Veuillez choisir une date valide');
                isValid = false;
            }
            
            const heure = document.getElementById('heure').value.trim();
            if (heure === '') {
                showError('heure', 'L\'heure est requise');
                isValid = false;
            } else if (!validateTime(heure)) {
                showError('heure', 'Veuillez choisir une heure valide');
                isValid = false;
            }
            
            const duree = document.getElementById('duree').value;
            if (duree === '') {
                showError('duree', 'La durée est requise');
                isValid = false;
            }
            
            const description = document.getElementById('description').value.trim();
            if (description === '') {
                showError('description', 'La description est requise');
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

        function validatePhone(phone) {
            const re = /^[\d\s\-\+\(\)]+$/;
            return re.test(phone) && phone.replace(/\D/g, '').length >= 8;
        }

        function validateDate(date) {
            const re = /^\d{4}-\d{2}-\d{2}$/;
            if (!re.test(date)) return false;
            const d = new Date(date);
            return d instanceof Date && !isNaN(d);
        }

        function validateTime(time) {
            const re = /^([01]\d|2[0-3]):([0-5]\d)$/;
            return re.test(time);
        }
    </script>
</body>
</html>
