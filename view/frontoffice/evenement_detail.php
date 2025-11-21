<?php
require_once __DIR__ . '/../../controller/EvenementC.php';
$ec = new EvenementC();

// Récupérer l'ID depuis l'URL
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de l'événement manquant.");
}

// Récupérer les détails de l'événement
$ev = $ec->getEvenementById($id);

if (!$ev) {
    die("Événement non trouvé.");
}

// Chemins pour images
$upload_web_dir = '/events/uploads/'; // Chemin public pour le navigateur
$upload_dir = realpath(__DIR__ . '/../../uploads/') . '/'; // Chemin serveur pour file_exists()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($ev['nom_evenement']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:#f7f9fc;
            padding:20px;
        }
        .container {
            max-width: 800px;
            margin:auto;
            background:white;
            padding:20px;
            border-radius:8px;
            box-shadow:0 2px 6px rgba(0,0,0,0.08);
        }
        img {
            max-width:100%;
            height:auto;
            border-radius:6px;
            border:1px solid #ddd;
            margin-bottom:20px;
        }
        .meta {
            color:#666;
            font-size:0.9em;
            margin-bottom:10px;
        }
        .badge {
            display:inline-block;
            padding:4px 8px;
            border-radius:12px;
            font-size:0.8em;
            color:white;
        }
        .available { background:#28a745; }
        .full { background:#dc3545; }
        .btn {
            background:#007bff;
            color:white;
            padding:8px 12px;
            border-radius:6px;
            text-decoration:none;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Bouton retour -->
    <a class="btn" href="/eventsCopy/view/frontoffice/evenements.php" style="margin-bottom:15px; display:inline-block;">← Retour aux événements</a>

    <!-- Titre de l'événement -->
    <h1><?= htmlspecialchars($ev['nom_evenement']) ?></h1>

    <!-- Image de l'événement -->
    <?php 
    $image_path = $upload_dir . $ev['image'];
    if (!empty($ev['image']) && file_exists($image_path)): 
    ?>
        <img src="<?= $upload_web_dir . rawurlencode($ev['image']) ?>" alt="<?= htmlspecialchars($ev['nom_evenement']) ?>">
    <?php else: ?>
        <div style="width:100%; height:300px; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; border-radius:6px;">
            Pas d'image
        </div>
    <?php endif; ?>

    <!-- Infos de l'événement -->
    <p class="meta">
        📅 Date : <?= htmlspecialchars($ev['date_evenement']) ?><br>
        Places : <?= (int)$ev['nombre_places'] ?> — Inscrits : <?= (int)$ev['nombre_inscrits'] ?>
    </p>

    <!-- Description -->
    <p>
        <?= nl2br(htmlspecialchars($ev['description'] ?? 'Pas de description disponible.')) ?>
    </p>

    <!-- Disponibilité -->
    <?php if ($ev['nombre_inscrits'] < $ev['nombre_places']): ?>
        <span class="badge available">Disponible</span>
    <?php else: ?>
        <span class="badge full">Complet</span>
    <?php endif; ?>
</div>
</body>
</html>
