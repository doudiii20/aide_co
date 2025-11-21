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
    <title>Gestion des Événements</title>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #1976d2, #0d47a1);
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 25px;
            display: flex;
            flex-direction: column;
            color: #fff;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
            animation: slideLeft 0.4s ease-out;
            z-index: 10;
        }

        @keyframes slideLeft {
            from {
                transform: translateX(-40px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .sidebar-header {
            text-align: center;
            padding: 10px;
        }

        .sidebar-logo {
            width: 70px;
            border-radius: 10px;
        }

        .sidebar-title {
            margin-top: 10px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #fff;
        }

        .sidebar-title span {
            color: #90caf9;
        }

        .sidebar-nav {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
        }

        .sidebar-link {
            padding: 14px 25px;
            color: #e3f2fd;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.28);
            font-weight: bold;
            color: #fff;
        }



        

        .main-content {
            margin-left: 260px; /* Décalage pour LIBÉRER la place */
            padding: 25px;
            min-height: 100vh;
        }


        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Arial;
            background: #f4f7fb;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #1976d2;
            margin-top: 20px;
            letter-spacing: 1px;
        }

        /* ---------------- TABLE ---------------- */
        .table {
            width: 95%;
            margin: 20px auto;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .table th {
            background: #1976d2;
            color: white;
            padding: 12px;
            text-transform: uppercase;
        }

        .table td {
            padding: 12px;
            background: white;
            border-bottom: 1px solid #e0e0e0;
        }

        .table tr:hover td {
            background: #f1f7ff;
        }

        img {
            max-width: 70px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        /* ---------------- BUTTONS ---------------- */
        .btn {
            padding: 7px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #1e7e34;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #b02a37;
        }

        .btn-outline {
            background: none;
            border: 2px solid #555;
            color: #333;
        }

        .btn-outline:hover {
            background: #333;
            color: white;
        }

        /* ---------------- FORMULAIRE ---------------- */
        form {
            width: 90%;
            max-width: 550px;
            margin: 40px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn-primary {
            background: #1976d2;
            border: none;
            padding: 10px 18px;
            margin-top: 20px;
            color: white;
            font-size: 16px;
            border-radius: 6px;
        }

        .btn-primary:hover {
            background: #0d47a1;
        }

        #msg {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>

</head>

<body>

   
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
            <a href="#" class="sidebar-link active">
                <i class="fa-solid fa-layer-group"></i> Consultation
            </a>

            <a href="http://localhost/eventsCopy/view/frontoffice/evenements.php" 
   class="sidebar-link">
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



    
    <div class="main-content">

        <h2>📅 LISTE DES ÉVÉNEMENTS</h2>

        <table class="table">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Date</th>
                <th>Places</th>
                <th>Inscrits</th>
                <th>Image</th>
                <th>Action</th>
            </tr>

            <?php foreach ($liste as $ev): ?>
            <tr>
                <td><?= $ev['id_evenement'] ?></td>
                <td><?= htmlspecialchars($ev['nom_evenement']) ?></td>
                <td><?= $ev['date_evenement'] ?></td>
                <td><?= $ev['nombre_places'] ?></td>
                <td><?= $ev['nombre_inscrits'] ?></td>
                <td>
                    <?php if (!empty($ev['image'])): ?>
                    <img src="../../uploads/<?= $ev['image'] ?>">
                    <?php else: ?>
                    --
                    <?php endif; ?>
                </td>
                <td>
                    <a class="btn btn-success"
                        href="manageEvenements.php?edit=<?= $ev['id_evenement'] ?>">Modifier</a>
                    <a class="btn btn-danger"
                        href="manageEvenements.php?delete=<?= $ev['id_evenement'] ?>"
                        onclick="return confirm('Supprimer cet événement ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <h2><?= $evenementToEdit ? "✏️ Modifier l'Événement" : "➕ Ajouter un Événement" ?></h2>

        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()" novalidate>
            <p id="msg"></p>

            <?php if ($evenementToEdit): ?>
            <input type="hidden" name="id_evenement" value="<?= $evenementToEdit['id_evenement'] ?>">
            <?php endif; ?>

            <label>Nom :</label>
            <input type="text" id="nom" name="nom_evenement"
                value="<?= $evenementToEdit['nom_evenement'] ?? '' ?>">

            <label>Date :</label>
            <input type="date" id="date" name="date_evenement"
                value="<?= $evenementToEdit['date_evenement'] ?? '' ?>">

            <label>Nombre de places :</label>
            <input type="number" id="places" name="nombre_places"
                value="<?= $evenementToEdit['nombre_places'] ?? '' ?>">

            <?php if ($evenementToEdit): ?>
            <label>Nombre d'inscrits :</label>
            <input type="number" name="nombre_inscrits" value="<?= $evenementToEdit['nombre_inscrits'] ?>">
            <?php endif; ?>

            <label>Image :</label>
            <input type="file" name="image">

            <?php if ($evenementToEdit && !empty($evenementToEdit['image'])): ?>
            <img src="../../uploads/<?= $evenementToEdit['image'] ?>" width="100">
            <?php endif; ?>

            <button class="btn-primary" type="submit"
                name="<?= $evenementToEdit ? 'update' : 'add' ?>">
                <?= $evenementToEdit ? "Mettre à jour" : "Ajouter" ?>
            </button>

            <?php if ($evenementToEdit): ?>
            <a href="manageEvenements.php" class="btn btn-outline">Annuler</a>
            <?php endif; ?>
        </form>

    </div> 


    <script>
        function validateForm() {
            let nom = document.getElementById("nom");
            let date = document.getElementById("date");
            let places = document.getElementById("places");
            let msg = document.getElementById("msg");

            nom.style.border = date.style.border = places.style.border = "";
            msg.innerHTML = "";
            msg.style.color = "red";

            if (nom.value.trim().length < 3) {
                msg.innerHTML = "Le nom doit contenir au moins 3 caractères.";
                nom.style.border = "2px solid red";
                return false;
            }

            if (!date.value) {
                msg.innerHTML = "Veuillez choisir une date.";
                date.style.border = "2px solid red";
                return false;
            }

            if (places.value <= 0) {
                msg.innerHTML = "Le nombre de places doit être supérieur à zéro.";
                places.style.border = "2px solid red";
                return false;
            }

            msg.style.color = "green";
            msg.innerHTML = "Formulaire valide ✔";
            return true;
        }
    </script>

</body>

</html>
