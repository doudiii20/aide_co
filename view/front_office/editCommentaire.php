<?php
// Cette page suppose que $commentaire_data est déjà rempli
// par index.php via le contrôleur.

// Sécuriser les variables au cas où
if (!isset($erreur)) {
    $erreur = "";
}

$id_commentaire = "";
$id_sujet       = "";
$contenu        = "";

// Vérifier que $commentaire_data existe avant de l'utiliser
if (isset($commentaire_data)) {

    if (isset($commentaire_data['id_commentaire'])) {
        $id_commentaire = $commentaire_data['id_commentaire'];
    }

    if (isset($commentaire_data['id_sujet'])) {
        $id_sujet = $commentaire_data['id_sujet'];
    }

    if (isset($commentaire_data['contenu'])) {
        $contenu = $commentaire_data['contenu'];
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">  
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Modifier commentaire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/novombre/assets/css/frontoffice.css">
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="/novombre/uploads/logoN.png" class="logo" alt="Logo">
                <h1 class="site-title">SUPPORTINI<span>.TN</span></h1>
            </div>
            <nav class="nav-links">
                <a href="/novombre/index.php?action=front_liste" class="nav-link">
                    <i class="fa-solid fa-list"></i> Sujets
                </a>
                <a href="/novombre/index.php?action=back_liste" class="nav-link">
                    BackOffice
                </a>
            </nav>
        </div>
    </header>

    <div class="main-content">

    <?php
    // Affichage du message d'erreur simple
    if ($erreur != "") {
        echo '<p id="msg" class="msg-error">'.$erreur.'</p>';
    }
    ?>

    <div class="form-container">
        <h2 class="form-title"><i class="fa-solid fa-pen"></i> Modifier le commentaire</h2>
        <form method="POST" action="/novombre/index.php?action=front_modifier_commentaire">
            <input type="hidden" name="id_commentaire" value="<?php echo $id_commentaire; ?>">
            <input type="hidden" name="id_sujet" value="<?php echo $id_sujet; ?>">

            <div class="form-group">
                <label class="form-label">Commentaire</label>
                <textarea class="form-control" name="contenu" rows="6"><?php echo $contenu; ?></textarea>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Modifier le commentaire</button>
                <a class="btn btn-outline"
                   href="/novombre/index.php?action=front_detail&id_sujet=<?php echo $id_sujet; ?>">
                    Annuler
                </a>
            </div>
        </form>
    </div>

    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2025 VotreApplication</p>
        </div>
    </footer>

    </div>

</body>
</html>
