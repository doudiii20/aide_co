<?php
require_once '../../controller/CategorieC.php';
require_once '../../model/Categorie.php';

$cc = new CategorieC();
$liste = $cc->listCategories();

$categorieToEdit = null;
if (isset($_GET['edit'])) {
    $categorieToEdit = $cc->getCategorie($_GET['edit']);
}

$errors = [];
if (isset($_POST['add']) || isset($_POST['update'])) {
    // Récupération et nettoyage des données
    $nom = trim($_POST['nom_categorie']);
    $desc = trim($_POST['description_categorie']);
    $date_creation = $_POST['date_creation'];
    $etat = $_POST['etat'];
    $nb_evenements = intval($_POST['nb_evenements'] ?? 0);
    $today = date('Y-m-d');

    // Validation côté serveur
    if (strlen($nom) < 3) $errors[] = "Le nom doit contenir au moins 3 caractères.";
    if (strlen($desc) < 3) $errors[] = "La description doit contenir au moins 3 caractères.";
    if ($date_creation < $today) $errors[] = "La date de création doit être aujourd'hui ou ultérieure.";
    if ($nb_evenements < 1) $errors[] = "Le nombre d'événements doit être supérieur ou égal à 1.";

    if (empty($errors)) {
        $categorie = new Categorie($nom, $desc, $date_creation, $etat, $nb_evenements);

        if (isset($_POST['add'])) {
            $cc->addCategorie($categorie);
        } elseif (isset($_POST['update'])) {
            $cc->updateCategorie($_POST['id_categorie'], $categorie);
        }

        header("Location: manageCategories.php");
        exit;
    }
}

if (isset($_GET['delete'])) {
    $cc->deleteCategorie($_GET['delete']);
    header("Location: manageCategories.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des Catégories</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ======= STYLE INSPIRÉ DE PATHÉ ======= */
:root {
  --primary: #d32f2f;
  --primary-dark: #b71c1c;
  --secondary: #212121;
  --light-gray: #f5f5f5;
  --medium-gray: #e0e0e0;
  --dark-gray: #757575;
  --white: #ffffff;
  --text: #212121;
  --text-light: #757575;
  --success: #4caf50;
  --warning: #ff9800;
  --error: #f44336;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Arial, sans-serif;
  background-color: var(--light-gray);
  color: var(--text);
  line-height: 1.6;
}

/* Header style Pathé */
.header {
  background-color: var(--primary);
  color: var(--white);
  padding: 15px 0;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.logo {
  font-size: 28px;
  font-weight: bold;
  letter-spacing: 1px;
}

.nav-menu {
  display: flex;
  gap: 25px;
}

.nav-link {
  color: var(--white);
  text-decoration: none;
  font-weight: 500;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-link:hover, .nav-link.active {
  background-color: rgba(255,255,255,0.1);
}

/* Main content */
.main-container {
  max-width: 1200px;
  margin: 30px auto;
  padding: 0 20px;
}

.page-title {
  font-size: 28px;
  margin-bottom: 25px;
  color: var(--secondary);
  font-weight: 600;
  border-bottom: 2px solid var(--primary);
  padding-bottom: 10px;
}

/* Card design */
.card {
  background: var(--white);
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  padding: 20px;
  margin-bottom: 25px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid var(--medium-gray);
}

.card-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--secondary);
}

/* Table styling */
.table {
  width: 100%;
  border-collapse: collapse;
  margin: 20px 0;
}

.table th {
  background-color: var(--primary);
  color: var(--white);
  padding: 12px 15px;
  text-align: left;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 14px;
  letter-spacing: 0.5px;
}

.table td {
  padding: 12px 15px;
  border-bottom: 1px solid var(--medium-gray);
}

.table tr:last-child td {
  border-bottom: none;
}

.table tr:hover {
  background-color: rgba(0,0,0,0.02);
}

/* Form styling */
.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: var(--secondary);
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--medium-gray);
  border-radius: 4px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.2);
}

/* Button styling */
.btn {
  display: inline-block;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  text-decoration: none;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 15px;
}

.btn-primary {
  background-color: var(--primary);
  color: var(--white);
}

.btn-primary:hover {
  background-color: var(--primary-dark);
}

.btn-success {
  background-color: var(--success);
  color: var(--white);
}

.btn-success:hover {
  background-color: #388e3c;
}

.btn-danger {
  background-color: var(--error);
  color: var(--white);
}

.btn-danger:hover {
  background-color: #d32f2f;
}

.btn-outline {
  background-color: transparent;
  border: 1px solid var(--dark-gray);
  color: var(--text);
}

.btn-outline:hover {
  background-color: var(--dark-gray);
  color: var(--white);
}

.btn-sm {
  padding: 6px 12px;
  font-size: 14px;
}

/* Alert/Message styling */
.alert {
  padding: 12px 15px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.alert-error {
  background-color: #ffebee;
  color: var(--error);
  border-left: 4px solid var(--error);
}

.alert-success {
  background-color: #e8f5e9;
  color: var(--success);
  border-left: 4px solid var(--success);
}

/* Grid system */
.row {
  display: flex;
  flex-wrap: wrap;
  margin: 0 -10px;
}

.col {
  flex: 1;
  padding: 0 10px;
}

.col-6 {
  flex: 0 0 50%;
  padding: 0 10px;
}

/* Responsive */
@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    gap: 15px;
  }
  
  .nav-menu {
    gap: 10px;
  }
  
  .col-6 {
    flex: 0 0 100%;
  }
  
  .table {
    display: block;
    overflow-x: auto;
  }
}

/* Badge styling */
.badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-active {
  background-color: #e8f5e9;
  color: var(--success);
}

.badge-inactive {
  background-color: #ffebee;
  color: var(--error);
}

/* Action buttons container */
.action-buttons {
  display: flex;
  gap: 8px;
}
</style>
</head>
<body>

<header class="header">
  <div class="header-container">
    <div class="logo">SUPPORTINI.TN</div>
    <nav class="nav-menu">
      <a href="#" class="nav-link active"><i class="fa-solid fa-layer-group"></i> Catégories</a>
      <a href="http://localhost/eventsCopy/view/frontoffice/categories.php" class="nav-link"><i class="fa-solid fa-list"></i> Frontoffice</a>
      <a href="/logout.php" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </nav>
  </div>
</header>

<div class="main-container">
  <h1 class="page-title">Gestion des Catégories</h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $err): ?>
        <p><?= $err ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Liste des Catégories</h2>
    </div>
    
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Description</th>
          <th>Date Création</th>
          <th>État</th>
          <th>Nb Événements</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($liste as $cat): ?>
        <tr>
          <td><?= $cat['id_categorie'] ?></td>
          <td><?= htmlspecialchars($cat['nom_categorie']) ?></td>
          <td><?= htmlspecialchars($cat['description_categorie']) ?></td>
          <td><?= $cat['date_creation'] ?></td>
          <td>
            <span class="badge <?= $cat['etat'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
              <?= $cat['etat'] ?>
            </span>
          </td>
          <td><?= $cat['nb_evenements'] ?></td>
          <td>
            <div class="action-buttons">
              <a class="btn btn-success btn-sm" href="manageCategories.php?edit=<?= $cat['id_categorie'] ?>">
                <i class="fa-solid fa-pen"></i> Modifier
              </a>
              <a class="btn btn-danger btn-sm" href="manageCategories.php?delete=<?= $cat['id_categorie'] ?>" onclick="return confirm('Supprimer cette catégorie ?');">
                <i class="fa-solid fa-trash"></i> Supprimer
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title"><?= $categorieToEdit ? "Modifier la Catégorie" : "Ajouter une Catégorie" ?></h2>
    </div>
    
    <form method="POST" novalidate onsubmit="return validateForm()">
      <?php if ($categorieToEdit): ?>
        <input type="hidden" name="id_categorie" value="<?= $categorieToEdit['id_categorie'] ?>">
      <?php endif; ?>

      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">Nom :</label>
            <input type="text" id="nom" name="nom_categorie" class="form-control" value="<?= $categorieToEdit['nom_categorie'] ?? '' ?>">
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">Date Création :</label>
            <input type="date" id="date" name="date_creation" class="form-control" value="<?= $categorieToEdit['date_creation'] ?? date('Y-m-d') ?>">
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description :</label>
        <input type="text" id="desc" name="description_categorie" class="form-control" value="<?= $categorieToEdit['description_categorie'] ?? '' ?>">
      </div>

      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">État :</label>
            <select name="etat" class="form-control">
              <option value="active" <?= ($categorieToEdit['etat'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="désactivée" <?= ($categorieToEdit['etat'] ?? '') === 'désactivée' ? 'selected' : '' ?>>Désactivée</option>
            </select>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">Nombre d'événements :</label>
            <input type="number" id="nb_evenements" name="nb_evenements" class="form-control" value="<?= $categorieToEdit['nb_evenements'] ?? 1 ?>" min="1">
          </div>
        </div>
      </div>

      <div class="form-group">
        <button class="btn btn-primary" type="submit" name="<?= $categorieToEdit ? 'update' : 'add' ?>">
          <i class="fa-solid <?= $categorieToEdit ? 'fa-check' : 'fa-plus' ?>"></i>
          <?= $categorieToEdit ? "Mettre à jour" : "Ajouter" ?>
        </button>

        <?php if ($categorieToEdit): ?>
          <a href="manageCategories.php" class="btn btn-outline">Annuler</a>
        <?php endif; ?>
      </div>
      
      <div id="msg"></div>
    </form>
  </div>
</div>

<script>
function validateForm() {
    let nom = document.getElementById("nom");
    let desc = document.getElementById("desc");
    let date = document.getElementById("date");
    let nbEv = document.getElementById("nb_evenements");
    let msg = document.getElementById("msg");

    // Reset styles
    nom.style.border = desc.style.border = date.style.border = nbEv.style.border = "";
    msg.innerHTML = "";
    msg.className = "";

    let isValid = true;

    if (nom.value.trim().length < 3) {
        msg.innerHTML = "Le nom doit contenir au moins 3 caractères.";
        msg.className = "alert alert-error";
        nom.style.border = "1px solid var(--error)";
        isValid = false;
    }
    
    if (desc.value.trim().length < 3) {
        msg.innerHTML = "La description doit contenir au moins 3 caractères.";
        msg.className = "alert alert-error";
        desc.style.border = "1px solid var(--error)";
        isValid = false;
    }

    let today = new Date().toISOString().split("T")[0];
    if (!date.value || date.value < today) {
        msg.innerHTML = "La date de création doit être aujourd'hui ou ultérieure.";
        msg.className = "alert alert-error";
        date.style.border = "1px solid var(--error)";
        isValid = false;
    }

    if (parseInt(nbEv.value) < 1) {
        msg.innerHTML = "Le nombre d'événements doit être supérieur ou égal à 1.";
        msg.className = "alert alert-error";
        nbEv.style.border = "1px solid var(--error)";
        isValid = false;
    }

    if (isValid) {
        msg.innerHTML = "Formulaire valide ✔";
        msg.className = "alert alert-success";
    }
    
    return isValid;
}
</script>
</body>
</html>