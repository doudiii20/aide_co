<?php
// Cette page suppose que $sujet_data est déjà rempli par index.php
// (via le contrôleur sujetController.php par exemple)

// On sécurise $erreur au cas où
if (!isset($erreur)) {
    $erreur = "";
}

// Valeurs par défaut
$id_sujet  = "";
$titre     = "";
$categorie = "";
$image     = "";

// On vérifie que $sujet_data existe avant de l'utiliser
if (isset($sujet_data)) {

    if (isset($sujet_data['id_sujet'])) {
        $id_sujet = $sujet_data['id_sujet'];
    }

    if (isset($sujet_data['titre'])) {
        $titre = $sujet_data['titre'];
    }

    if (isset($sujet_data['categorie'])) {
        $categorie = $sujet_data['categorie'];
    }

    if (isset($sujet_data['image'])) {
        $image = $sujet_data['image'];
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>BackOffice – Modifier sujet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/novombre/assets/css/backoffice.css">
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/novombre/uploads/logoN.png" class="sidebar-logo" alt="Logo">
            <h2 class="sidebar-title">SUPPORTINI<span>.TN</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-table-cells-large"></i> Dashboard
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-user"></i> Utilisateurs
            </a>
            <a href="/novombre/index.php?action=back_liste" class="sidebar-link active">
                <i class="fa-solid fa-layer-group"></i> Forum
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Consultation
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-calendar"></i> Événements
            </a>
        </nav>
    </aside>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-pen"></i> Modifier un sujet</h1>
        </div>

        <div class="form-container">
            <?php
            // Affichage du message d'erreur simple
            if ($erreur != "") {
                echo '<p id="msg" class="msg-error">'.$erreur.'</p>';
            }
            ?>

            <!-- IMPORTANT : enctype pour upload fichier -->
            <form method="POST"
      action="/novombre/index.php?action=back_modifier"
      enctype="multipart/form-data">

    <input type="hidden" name="id_sujet" value="<?php echo $id_sujet; ?>">

    <div class="form-group">
        <label class="form-label">Titre</label>
        <input class="form-control"
               type="text"
               name="titre"
               value="<?php echo $titre; ?>">
    </div>

    <div class="form-group">
        <label class="form-label">Catégorie</label>
        <input class="form-control"
               type="text"
               name="categorie"
               value="<?php echo $categorie; ?>">
    </div>

    <!-- Image actuelle (optionnel) -->
    <?php if ($image != "") { ?>
    <div class="form-group">
        <label class="form-label">Image actuelle</label>
        <div>
            <img src="<?php echo $image; ?>" style="max-width:200px;border-radius:8px;">
        </div>
    </div>
    <?php } ?>

    <!-- Nouvelle image -->
    <div class="form-group">
        <label class="form-label">Nouvelle image (facultatif)</label>
        <input class="form-control"
               type="file"
               name="image"
               accept="image/*">
        <p class="form-help">Laisser vide pour garder l'image actuelle.</p>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Modifier</button>
        <a class="btn btn-outline" href="/novombre/index.php?action=back_liste">Annuler</a>
    </div>
</form>

        </div>
    </div>

</body>
</html>
