<?php
require_once __DIR__ . '/../../controller/EvenementC.php';
$ec = new EvenementC();

$name = $_GET['name'] ?? null;
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$availability = $_GET['availability'] ?? null;

if ($name || $date_from || $date_to || $availability) {
    $liste = $ec->filterEvenements($name, $date_from, $date_to, $availability);
} else {
    $liste = $ec->listEvenements();
}

// chemin public
$upload_web_dir = '/events/uploads/';
$base_url = '/events';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Événements</title>

    <!-- ======================== CSS GLOBAL ========================= -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(to right, #e3f2fd, #ffffff);
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
        }

        /* ===================== SIDEBAR ===================== */
        .sidebar {
            width: 240px;
            background: #0d47a1;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
            color: white;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
        }

        .sidebar img {
            width: 140px;
            margin: 0 auto 25px auto;
            display: block;
        }

        .sidebar-link {
            display: block;
            padding: 14px 25px;
            font-size: 17px;
            color: white;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-link:hover {
            background: rgba(255,255,255,0.15);
            padding-left: 35px;
        }

        /* ===================== PAGE CONTENT ===================== */
        .content {
            margin-left: 240px;
            width: calc(100% - 240px);
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .header {
            background: #1976d2;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
        }

        /* ===================== FILTRES ===================== */
        form.filters {
            background: #ffffff;
            padding: 20px;
            margin: 25px auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 900px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            flex: 1;
            min-width: 180px;
        }

        button[type="submit"] {
            background: #1976d2;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        button[type="submit"]:hover {
            background: #115293;
        }

        .reset-link {
            color: #666;
            text-decoration: none;
            padding: 10px;
        }

        /* ===================== EVENTS LIST ===================== */
        .events-container {
            padding: 20px;
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }

        .event-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: 0.3s ease-in-out;
        }

        .event-card:hover {
            transform: translateY(-6px);
        }

        .event-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .event-info { padding: 18px; }
        .event-title {
            font-size: 20px;
            font-weight: bold;
            color: #0d47a1;
            margin-bottom: 8px;
        }

        .event-meta {
            color: #666;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            color: white;
        }
        .available { background: #28a745; }
        .full { background: #dc3545; }

        .btn-view {
            display: inline-block;
            margin-top: 12px;
            background: #1976d2;
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        /* ===================== DARK MODE ===================== */
        .dark-mode {
            background: #121212 !important;
            color: #e0e0e0 !important;
        }
        .dark-mode .event-card {
            background: #1e1e1e;
        }

        #toggle-dark {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #1976d2;
            color: white;
            padding: 12px 15px;
            border-radius: 50%;
            cursor: pointer;
            border: none;
            font-size: 20px;
        }

    </style>
</head>

<body>

<!-- ======================== SIDEBAR ========================= -->
<div class="sidebar">
    <img src="http://localhost/eventsCopy/uploads/logo1.png" alt="Logo">

    <a href="http://localhost/eventsCopy/view/frontoffice/evenements.php?name=&date_from=&date_to=&availability=" 
       class="sidebar-link">
       📅 Événements
    </a>

    <a href="#" class="sidebar-link">👤 Profil</a>
    <a href="#" class="sidebar-link">⚙ Paramètres</a>
    <a href="/logout.php" class="sidebar-link">
    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
</a>
</div>

<!-- ======================== CONTENT ========================= -->
<div class="content">

<div class="header">📅 Liste des Événements</div>

<button id="toggle-dark">🌙</button>

<!-- ========================== FILTRES ========================== -->
<form method="GET" class="filters">
    <input type="text" name="name" placeholder="Recherche par nom..." value="<?= htmlspecialchars($name ?? '') ?>">
    <input type="date" name="date_from" value="<?= htmlspecialchars($date_from ?? '') ?>">
    <input type="date" name="date_to" value="<?= htmlspecialchars($date_to ?? '') ?>">
    <select name="availability">
        <option value="">Tous</option>
        <option value="available" <?= ($availability=='available')?'selected':'' ?>>Disponible</option>
        <option value="full" <?= ($availability=='full')?'selected':'' ?>>Complet</option>
    </select>
    <button type="submit">Filtrer</button>
    <a class="reset-link" href="evenements.php">Réinitialiser</a>
</form>

<!-- ========================== LISTE EVENTS ========================== -->
<div class="events-container">

    <?php if (count($liste) == 0): ?>
        <p style="text-align:center;font-size:18px;color:#888;">Aucun événement trouvé.</p>
    <?php endif; ?>

    <?php foreach ($liste as $ev): ?>
        <div class="event-card">

            <?php if (!empty($ev['image'])): ?>
                <img src="<?= $upload_web_dir . rawurlencode($ev['image']) ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/300x180?text=Pas+d%27image">
            <?php endif; ?>

            <div class="event-info">
                <div class="event-title"><?= htmlspecialchars($ev['nom_evenement']) ?></div>

                <div class="event-meta">
                    📅 <?= htmlspecialchars($ev['date_evenement']) ?><br>
                    Places : <?= (int)$ev['nombre_places'] ?> — Inscrits : <?= (int)$ev['nombre_inscrits'] ?>
                </div>

                <?php if ($ev['nombre_inscrits'] < $ev['nombre_places']): ?>
                    <span class="badge available">Disponible</span>
                <?php else: ?>
                    <span class="badge full">Complet</span>
                <?php endif; ?>

                <br>
                <a class="btn-view" 
                   href="http://localhost/eventsCopy/view/frontoffice/evenement_detail.php?id=<?= $ev['id_evenement'] ?>">
                   Voir détails
                </a>
            </div>
        </div>
    <?php endforeach; ?>

</div>

</div>

<!-- DARK MODE -->
<script>
document.getElementById('toggle-dark').onclick = function () {
    document.body.classList.toggle('dark-mode');
};
</script>

</body>
</html>
