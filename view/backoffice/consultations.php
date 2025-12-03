<?php
require_once __DIR__ . '/../../auth/config.php';
require_once __DIR__ . '/../../model/Consultation.php';
require_once __DIR__ . '/../../controller/ConsultationController.php';

$controller = new RendezVousController($pdo);
$message = '';
$messageType = '';

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

$rendezVous = $controller->getAllRendezVous();
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
        .form-label i {
            margin-right: 8px;
            color: var(--primary-red);
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
            border-color: var(--primary-red);
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
        .date-picker, .time-picker {
            cursor: pointer;
            position: relative;
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
        /* Flatpickr dark theme customization */
        .flatpickr-calendar {
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5) !important;
        }
        .flatpickr-months {
            background: var(--primary-red) !important;
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
            border-color: var(--primary-red) !important;
        }
        .flatpickr-day.selected {
            background: var(--primary-red) !important;
            border-color: var(--primary-red) !important;
        }
        .flatpickr-day.today {
            border-color: var(--primary-red) !important;
        }
        .flatpickr-time input {
            color: var(--text-light) !important;
            background: var(--card-bg) !important;
        }
        .flatpickr-time .flatpickr-time-separator {
            color: var(--text-light) !important;
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
        .text-muted {
            color: var(--text-muted);
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
            <a href="consultations.php" class="sidebar-link active">
                <i class="fa-solid fa-calendar-check"></i> Rendez-vous
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-calendar"></i> Événements
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Catégories
            </a>
            <a href="compteRendus.php" class="sidebar-link">
                <i class="fa-solid fa-file-alt"></i> Comptes Rendus
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-exclamation-circle"></i> Réclamation
            </a>
            <a href="/logout.php" class="sidebar-link">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-calendar-check"></i> Gestion des Rendez-vous</h1>
</div>

<?php if($message): ?>
            <div class="message <?= $messageType === 'success' ? 'msg-success' : 'msg-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
<?php endif; ?>

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
                        <a href="consultations.php" class="btn btn-outline">
                            <i class="fa-solid fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Durée</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rendezVous)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted);">
                                Aucun rendez-vous disponible
                            </td>
    </tr>
                    <?php else: ?>
                        <?php foreach($rendezVous as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['id_rendezVous']) ?></td>
                                <td><?= htmlspecialchars($r['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($r['email_universitaire']) ?></td>
                                <td><?= htmlspecialchars($r['telephone']) ?></td>
                                <td><?= htmlspecialchars($r['type_rendezVous']) ?></td>
                                <td><?= htmlspecialchars($r['date_souhaitee']) ?></td>
                                <td><?= htmlspecialchars($r['heure']) ?></td>
                                <td><?= htmlspecialchars($r['duree']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?= $r['id_rendezVous'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-pen"></i> Modifier
                                        </a>
                                        <a href="?delete=<?= $r['id_rendezVous'] ?>" 
                                           onclick="return confirm('Supprimer ce rendez-vous ?')" 
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
