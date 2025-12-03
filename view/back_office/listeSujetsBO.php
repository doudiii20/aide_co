<?php
// Si la vue est ouverte directement (sans passer par index.php),
// on charge la config + le contrôleur et on récupère la liste
if (!isset($liste)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/sujetController.php';

    $crud  = new SujetCRUD();
    $liste = $crud->getAll();

    // Valeurs par défaut si on passe directement par le fichier
    $totalSujets = count($liste);
    $page        = 1;
    $totalPages  = 1;
}

// Sécuriser les variables utilisées dans la vue
if (!isset($totalSujets)) {
    $totalSujets = count($liste);
}
if (!isset($page)) {
    $page = 1;
}
if (!isset($totalPages)) {
    $totalPages = 1;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>BackOffice – Sujets</title>
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
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Catégories
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-exclamation-circle"></i> Reclamation
            </a>
            <a href="/logout.php" class="sidebar-link">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </a>
        </nav>
    </aside>

    <!-- Contenu principal -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fa-solid fa-table"></i> Gestion des Sujets
            </h1>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-list"></i></div>
                <div class="stat-value"><?php echo $totalSujets; ?></div>
                <div class="stat-label">Total des sujets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-check"></i></div>
                <div class="stat-value"><?php echo count($liste); ?></div>
                <div class="stat-label">Sujets affichés (page)</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-value">0</div>
                <div class="stat-label">Utilisateurs</div>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Date création</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (!isset($liste) || count($liste) == 0) {
                    ?>
                    <tr>
                        <td colspan="5">Aucun sujet.</td>
                    </tr>
                    <?php
                } else {

                    foreach ($liste as $sujet) {

                        $id_sujet      = "";
                        $titre         = "";
                        $categorie     = "";
                        $date_creation = "";

                        if (isset($sujet['id_sujet'])) {
                            $id_sujet = $sujet['id_sujet'];
                        }

                        if (isset($sujet['titre'])) {
                            $titre = $sujet['titre'];
                        }

                        if (isset($sujet['categorie'])) {
                            $categorie = $sujet['categorie'];
                        }

                        if (isset($sujet['date_creation'])) {
                            $date_creation = $sujet['date_creation'];
                        }
                        ?>
                        <tr>
                            <td><?php echo $id_sujet; ?></td>
                            <td><?php echo $titre; ?></td>
                            <td><?php echo $categorie; ?></td>
                            <td><?php echo $date_creation; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a class="btn btn-primary btn-sm"
                                       href="/novombre/index.php?action=back_edit_form&id_sujet=<?php echo $id_sujet; ?>">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <a class="btn btn-danger btn-sm"
                                       href="/novombre/index.php?action=back_supprimer&id_sujet=<?php echo $id_sujet; ?>"
                                       onclick="return confirm('Supprimer ce sujet ?');">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1) { ?>
        <div class="pagination">
            <?php if ($page > 1) { ?>
                <a class="page-link"
                   href="/novombre/index.php?action=back_liste&page=<?php echo $page - 1; ?>">
                    &laquo; Précédent
                </a>
            <?php } ?>

            <?php
            $i = 1;
            while ($i <= $totalPages) {
                if ($i == $page) {
                    ?>
                    <span class="page-current"><?php echo $i; ?></span>
                    <?php
                } else {
                    ?>
                    <a class="page-link"
                       href="/novombre/index.php?action=back_liste&page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php
                }
                $i = $i + 1;
            }
            ?>

            <?php if ($page < $totalPages) { ?>
                <a class="page-link"
                   href="/novombre/index.php?action=back_liste&page=<?php echo $page + 1; ?>">
                    Suivant &raquo;
                </a>
            <?php } ?>
        </div>
        <?php } ?>

        <p>
            <a class="btn btn-outline" href="/novombre/index.php?action=front_liste">
                Retour au front office
            </a>
        </p>
    </div>

</body>
</html>
