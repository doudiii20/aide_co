<?php
require_once '../../controller/EvenementC.php';
require_once '../../model/Evenement.php';

$ec = new EvenementC();
$liste = $ec->listEvenements();


$upload_dir = __DIR__ . '/../../uploads/';


$evenementToEdit = null;
if (isset($_GET['edit'])) {
    $evenementToEdit = $ec->getEvenement($_GET['edit']);
}


if (isset($_POST['add'])) {

    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $imageName = uniqid('ev_') . "." . $ext;
            $target = $upload_dir . $imageName;

            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        }
    }

    $e = new Evenement(
        $_POST['nom_evenement'],
        $_POST['date_evenement'],
        $_POST['nombre_places'],
        0,
        $imageName
    );

    $ec->addEvenement($e);
    header("Location: manageEvenements.php");
    exit;
}

// ---- Mise à jour ----
if (isset($_POST['update'])) {

    $imageName = $evenementToEdit['image'] ?? null;

    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $imageName = uniqid('ev_') . "." . $ext;
            $target = $upload_dir . $imageName;

            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        }
    }

    $e = new Evenement(
        $_POST['nom_evenement'],
        $_POST['date_evenement'],
        $_POST['nombre_places'],
        $_POST['nombre_inscrits'],
        $imageName
    );

    $ec->updateEvenement($_POST['id_evenement'], $e);
    header("Location: manageEvenements.php");
    exit;
}

// ---- Suppression ----
if (isset($_GET['delete'])) {
    $ec->deleteEvenement($_GET['delete']);
    header("Location: manageEvenements.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Événements | Supportini</title>
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
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .event-image {
            width: 70px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
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

        .current-image {
            margin-top: 10px;
        }

        .current-image img {
            max-width: 150px;
            border-radius: 4px;
        }

        /* Message */
        #msg {
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

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .stat-icon {
            font-size: 30px;
            margin-bottom: 15px;
            color: var(--primary-red);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../uploads/logo1.png" class="sidebar-logo" alt="Logo">
            <h2 class="sidebar-title">SUPPORTINI<span>.TN</span></h2>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-table-cells-large"></i> Dashboard
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-user"></i> Utilisateurs
            </a>
            <a href="#" class="sidebar-link active">
                <i class="fa-solid fa-layer-group"></i> Forom
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Consultation
            </a>
            <a href="http://localhost/eventsCopy/view/frontoffice/evenements.php" class="sidebar-link">
                <i class="fa-solid fa-calendar"></i> Événements
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-exclamation-circle"></i> Reclamation
            </a>
            <a href="/logout.php" class="sidebar-link">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-calendar-days"></i> Gestion des Événements</h1>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-film"></i>
                </div>
                <div class="stat-value"><?= count($liste) ?></div>
                <div class="stat-label">Événements total</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="stat-value">
                    <?php
                    $totalPlaces = 0;
                    foreach ($liste as $ev) {
                        $totalPlaces += $ev['nombre_places'];
                    }
                    echo $totalPlaces;
                    ?>
                </div>
                <div class="stat-label">Places totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-value">
                    <?php
                    $totalInscrits = 0;
                    foreach ($liste as $ev) {
                        $totalInscrits += $ev['nombre_inscrits'];
                    }
                    echo $totalInscrits;
                    ?>
                </div>
                <div class="stat-label">Inscrits total</div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Places</th>
                        <th>Inscrits</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($liste as $ev): ?>
                    <tr>
                        <td><?= $ev['id_evenement'] ?></td>
                        <td><?= htmlspecialchars($ev['nom_evenement']) ?></td>
                        <td><?= date('d/m/Y', strtotime($ev['date_evenement'])) ?></td>
                        <td><?= $ev['nombre_places'] ?></td>
                        <td><?= $ev['nombre_inscrits'] ?></td>
                        <td>
                            <?php if (!empty($ev['image'])): ?>
                            <img src="../../uploads/<?= $ev['image'] ?>" class="event-image" alt="Image événement">
                            <?php else: ?>
                            <span class="text-muted">--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a class="btn btn-primary btn-sm" href="manageEvenements.php?edit=<?= $ev['id_evenement'] ?>">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>
                                <a class="btn btn-danger btn-sm" href="manageEvenements.php?delete=<?= $ev['id_evenement'] ?>" onclick="return confirm('Supprimer cet événement ?');">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Form -->
        <div class="form-container">
            <h2 class="form-title">
                <i class="fa-solid <?= $evenementToEdit ? 'fa-pen' : 'fa-plus' ?>"></i>
                <?= $evenementToEdit ? "Modifier l'Événement" : "Ajouter un Événement" ?>
            </h2>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()" novalidate>
                <p id="msg"></p>

                <?php if ($evenementToEdit): ?>
                <input type="hidden" name="id_evenement" value="<?= $evenementToEdit['id_evenement'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="nom">Nom de l'événement</label>
                    <input type="text" id="nom" name="nom_evenement" class="form-control" 
                        value="<?= $evenementToEdit['nom_evenement'] ?? '' ?>" placeholder="Entrez le nom de l'événement">
                </div>

                <div class="form-group">
                    <label class="form-label" for="date">Date de l'événement</label>
                    <input type="date" id="date" name="date_evenement" class="form-control"
                        value="<?= $evenementToEdit['date_evenement'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="places">Nombre de places</label>
                    <input type="number" id="places" name="nombre_places" class="form-control"
                        value="<?= $evenementToEdit['nombre_places'] ?? '' ?>" placeholder="Nombre de places disponibles">
                </div>

                <?php if ($evenementToEdit): ?>
                <div class="form-group">
                    <label class="form-label" for="inscrits">Nombre d'inscrits</label>
                    <input type="number" id="inscrits" name="nombre_inscrits" class="form-control"
                        value="<?= $evenementToEdit['nombre_inscrits'] ?>" placeholder="Nombre d'inscrits actuels">
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="image">Image de l'événement</label>
                    <input type="file" id="image" name="image" class="form-control">
                    
                    <?php if ($evenementToEdit && !empty($evenementToEdit['image'])): ?>
                    <div class="current-image">
                        <p>Image actuelle :</p>
                        <img src="../../uploads/<?= $evenementToEdit['image'] ?>" alt="Image actuelle">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit" name="<?= $evenementToEdit ? 'update' : 'add' ?>">
                        <i class="fa-solid <?= $evenementToEdit ? 'fa-check' : 'fa-plus' ?>"></i>
                        <?= $evenementToEdit ? "Mettre à jour" : "Ajouter l'événement" ?>
                    </button>
                    
                    <?php if ($evenementToEdit): ?>
                    <a href="manageEvenements.php" class="btn btn-outline">
                        <i class="fa-solid fa-times"></i> Annuler
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            let nom = document.getElementById("nom");
            let date = document.getElementById("date");
            let places = document.getElementById("places");
            let msg = document.getElementById("msg");

            // Reset styles
            nom.classList.remove('error');
            date.classList.remove('error');
            places.classList.remove('error');
            msg.innerHTML = "";
            msg.className = "";

            // Validation
            let isValid = true;

            if (nom.value.trim().length < 3) {
                msg.innerHTML = "Le nom doit contenir au moins 3 caractères.";
                msg.className = "msg-error";
                nom.classList.add('error');
                nom.style.borderColor = "#d32f2f";
                isValid = false;
            } else {
                nom.style.borderColor = "";
            }

            if (!date.value) {
                msg.innerHTML = "Veuillez choisir une date.";
                msg.className = "msg-error";
                date.classList.add('error');
                date.style.borderColor = "#d32f2f";
                isValid = false;
            } else {
                date.style.borderColor = "";
            }

            if (places.value <= 0) {
                msg.innerHTML = "Le nombre de places doit être supérieur à zéro.";
                msg.className = "msg-error";
                places.classList.add('error');
                places.style.borderColor = "#d32f2f";
                isValid = false;
            } else {
                places.style.borderColor = "";
            }

            if (isValid) {
                msg.innerHTML = "Formulaire valide ✔";
                msg.className = "msg-success";
            }

            return isValid;
        }

        // Add error class styling
        const style = document.createElement('style');
        style.textContent = `
            .form-control.error {
                border-color: #d32f2f !important;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>