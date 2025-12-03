<?php
// Cette page suppose que $sujet_data et $liste_commentaires
// sont déjà remplis par index.php via le contrôleur.

// Sécuriser quelques variables
if (!isset($message)) {
    $message = "";
}
if (!isset($erreur)) {
    $erreur = "";
}
if (!isset($pageComm)) {
    $pageComm = 1;
}
if (!isset($totalPagesComm)) {
    $totalPagesComm = 1;
}
if (!isset($totalCommentaires)) {
    if (isset($liste_commentaires)) {
        $totalCommentaires = count($liste_commentaires);
    } else {
        $totalCommentaires = 0;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Détail du sujet</title>
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
    if ($message != "") {
        echo '<p id="msg" class="msg-success">'.$message.'</p>';
    }
    if ($erreur != "") {
        echo '<p id="msg" class="msg-error">'.$erreur.'</p>';
    }

    if (!isset($sujet_data) || !$sujet_data) {
        echo "<p>Aucun sujet à afficher. Revenez à la liste.</p>";
    } else {

        $id_sujet  = "";
        $titre     = "";
        $categorie = "";

        if (isset($sujet_data['id_sujet'])) {
            $id_sujet = $sujet_data['id_sujet'];
        }
        if (isset($sujet_data['titre'])) {
            $titre = $sujet_data['titre'];
        }
        if (isset($sujet_data['categorie'])) {
            $categorie = $sujet_data['categorie'];
        }
    ?>
        <div class="page-header">
            <h1 class="page-title">
                <i class="fa-solid fa-comments"></i> <?php echo $titre; ?>
            </h1>
            <p class="page-subtitle">Catégorie : <?php echo $categorie; ?></p>
        </div>

        <div class="form-container">
            <h2 class="form-title"><i class="fa-solid fa-plus"></i> Ajouter un commentaire</h2>
            <form method="POST" action="/novombre/index.php?action=front_ajouter_commentaire">
                <input type="hidden" name="id_sujet" value="<?php echo $id_sujet; ?>">

                <div class="form-group">
                    <label class="form-label">Commentaire</label>
                    <textarea class="form-control" name="contenu" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Publier le commentaire</button>
                </div>
            </form>
        </div>

        <h2 style="margin-top:20px;">Commentaires (<?php echo $totalCommentaires; ?>)</h2>
        <?php
        if (!isset($liste_commentaires) || empty($liste_commentaires)) {
            echo "<p>Aucun commentaire.</p>";
        } else {
        ?>
            <div class="items-grid">
                <?php
                foreach ($liste_commentaires as $c) {

                    $id_commentaire   = "";
                    $contenu          = "";
                    $date_commentaire = "";

                    if (isset($c['id_commentaire'])) {
                        $id_commentaire = $c['id_commentaire'];
                    }
                    if (isset($c['contenu'])) {
                        $contenu = $c['contenu'];
                    }
                    if (isset($c['date_commentaire'])) {
                        $date_commentaire = $c['date_commentaire'];
                    }
                ?>
                    <div class="item-card">
                        <div class="item-content">
                            <div class="item-title"><?php echo $contenu; ?></div>
                            <div class="item-meta">
                                <div class="item-meta-item">
                                    <i class="fa-regular fa-calendar"></i> <?php echo $date_commentaire; ?>
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                <a class="btn btn-outline"
                                   href="/novombre/index.php?action=front_edit_form_commentaire&id_commentaire=<?php echo $id_commentaire; ?>&id_sujet=<?php echo $id_sujet; ?>">
                                    Modifier
                                </a>
                                <a class="btn btn-danger"
                                   href="/novombre/index.php?action=front_supprimer_commentaire&id_commentaire=<?php echo $id_commentaire; ?>&id_sujet=<?php echo $id_sujet; ?>"
                                   onclick="return confirm('Supprimer ce commentaire ?');">
                                    Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>

            <!-- PAGINATION COMMENTAIRES -->
            <?php if ($totalPagesComm > 1) { ?>
            <div class="pagination" style="margin-top:15px;">
                <?php if ($pageComm > 1) { ?>
                    <a class="page-link"
                       href="/novombre/index.php?action=front_detail&id_sujet=<?php echo $id_sujet; ?>&page_commentaire=<?php echo $pageComm - 1; ?>">
                        &laquo; Précédent
                    </a>
                <?php } ?>

                <?php
                $i = 1;
                while ($i <= $totalPagesComm) {

                    if ($i == $pageComm) {
                        ?>
                        <span class="page-current"><?php echo $i; ?></span>
                        <?php
                    } else {
                        ?>
                        <a class="page-link"
                           href="/novombre/index.php?action=front_detail&id_sujet=<?php echo $id_sujet; ?>&page_commentaire=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php
                    }

                    $i = $i + 1;
                }
                ?>

                <?php if ($pageComm < $totalPagesComm) { ?>
                    <a class="page-link"
                       href="/novombre/index.php?action=front_detail&id_sujet=<?php echo $id_sujet; ?>&page_commentaire=<?php echo $pageComm + 1; ?>">
                        Suivant &raquo;
                    </a>
                <?php } ?>
            </div>
            <?php } ?>

        <?php
        } // fin else commentaires
    } // fin else sujet
    ?>

    <p style="margin-top:20px;">
        <a class="btn btn-outline" href="/novombre/index.php?action=front_liste">
            Retour à la liste des sujets
        </a>
    </p>

    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2025 VotreApplication</p>
        </div>
    </footer>

    </div>

</body>
</html>
