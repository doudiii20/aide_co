<?php
// =========================================================
// VUE FRONT : Liste des sujets (SUPPORTINI.TN)
// =========================================================

// Sécuriser / initialiser les variables si la vue est appelée directement
$liste       = isset($liste) ? $liste : array();
$message     = isset($message) ? $message : "";
$erreur      = isset($erreur) ? $erreur : "";
$totalSujets = isset($totalSujets) ? $totalSujets : count($liste);
$page        = isset($page) ? $page : 1;
$totalPages  = isset($totalPages) ? $totalPages : 1;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Liste des Sujets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/novombre/assets/css/frontoffice.css">
</head>
<body>

    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="/novombre/uploads/logoN.png" class="logo" alt="Logo">
                <h1 class="site-title">SUPPORTINI<span>.TN</span></h1>
            </div>
            <nav class="nav-links">
                <a href="/novombre/index.php?action=front_liste" class="nav-link active">
                    <i class="fa-solid fa-list"></i> Sujets
                </a>
                <a href="/novombre/index.php?action=back_liste" class="nav-link">
                    <i class="fa-solid fa-user"></i> BackOffice
                </a>
            </nav>
        </div>
    </header>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-list"></i> Liste des Sujets</h1>
            <p class="page-subtitle">Total : <?php echo $totalSujets; ?> sujets</p>
        </div>

        <?php if ($message != "") { ?>
            <p id="msg" class="msg-success"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <?php if ($erreur != "") { ?>
            <p id="msg" class="msg-error"><?php echo htmlspecialchars($erreur); ?></p>
        <?php } ?>

        <div class="search-container">
            <h2 class="search-title"><i class="fa-solid fa-plus"></i> Ajouter un sujet</h2>

            <!-- Formulaire avec upload image -->
            <form method="POST"
                  action="/novombre/index.php?action=front_ajouter"
                  class="search-form"
                  enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Titre</label>
                    <input class="form-control" type="text" name="titre">
                </div>
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <input class="form-control" type="text" name="categorie">
                </div>
                <div class="form-group">
                    <label class="form-label">Image (optionnel)</label>
                    <input class="form-control" type="file" name="image">
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Ajouter</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($liste)) { ?>
                    <tr>
                        <td colspan="4">Aucun sujet.</td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($liste as $s) {

                        // On suppose que le contrôleur fournit toujours ces colonnes
                        $id_sujet  = isset($s['id_sujet'])  ? $s['id_sujet']  : "";
                        $titre     = isset($s['titre'])     ? $s['titre']     : "";
                        $categorie = isset($s['categorie']) ? $s['categorie'] : "";
                        $image     = isset($s['image'])     ? $s['image']     : "";

                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($id_sujet); ?></td>
                            <td>
                                <a href="/novombre/index.php?action=front_detail&id_sujet=<?php echo htmlspecialchars($id_sujet); ?>">
                                    <?php echo htmlspecialchars($titre); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($categorie); ?></td>
                            <td>
                                <?php if ($image != "") {
                                    $src = "/novombre/" . htmlspecialchars($image);
                                ?>
                                    <img src="<?php echo $src; ?>" alt="Image"
                                         style="max-width:60px; height:auto; border-radius:4px;">
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1) { ?>
            <div class="pagination">

                <?php if ($page > 1) { ?>
                    <a class="page-link"
                       href="/novombre/index.php?action=front_liste&page=<?php echo $page - 1; ?>">
                        &laquo; Précédent
                    </a>
                <?php } ?>

                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                    <?php if ($i == $page) { ?>
                        <span class="page-current"><?php echo $i; ?></span>
                    <?php } else { ?>
                        <a class="page-link"
                           href="/novombre/index.php?action=front_liste&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php } ?>
                <?php } ?>

                <?php if ($page < $totalPages) { ?>
                    <a class="page-link"
                       href="/novombre/index.php?action=front_liste&page=<?php echo $page + 1; ?>">
                        Suivant &raquo;
                    </a>
                <?php } ?>

            </div>
        <?php } ?>

        <footer class="main-footer">
            <div class="footer-content">
                <p class="footer-text">&copy; 2025 VotreApplication</p>
            </div>
        </footer>
    </div>

</body>
</html>
