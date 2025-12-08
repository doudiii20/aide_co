<?php
require_once __DIR__ . '/../../auth/config.php';
require_once __DIR__ . '/../../controller/CompteRenduController.php';

$controller = new CompteRenduController($pdo);

// Récupérer les statistiques Comptes Rendus
$totalComptesRendus = $controller->getTotalComptesRendus();
$totalConsultations = $controller->getTotalConsultations();
$statsByStatut = $controller->getStatsByStatut();
$comptesRendusParMois = $controller->getComptesRendusParMois();
$recentComptesRendus = $controller->getRecentComptesRendus(5);
$consultationsSansCompteRendu = $controller->getConsultationsSansCompteRendu();
$pourcentageLiaison = $controller->getPourcentageLiaison();

// Récupérer les statistiques Rendez-vous
$consultationsAujourdhui = $controller->getConsultationsAujourdhui();
$consultationsCetteSemaine = $controller->getConsultationsCetteSemaine();
$consultationsCeMois = $controller->getConsultationsCeMois();
$consultationsParMois = $controller->getConsultationsParMois();
$consultationsParMotif = $controller->getConsultationsParMotif();
$prochainesConsultations = $controller->getProchainesConsultations(5);

// Préparer les données pour les graphiques
$statutLabels = [];
$statutData = [];
$statutColors = [
    'rendezVous_termine' => '#4caf50',
    'autre_rendezVous_necessaire' => '#ff9800',
    'suivi_recommande' => '#2196f3'
];
$statutNoms = [
    'rendezVous_termine' => 'RDV Terminé',
    'autre_rendezVous_necessaire' => 'Autre RDV nécessaire',
    'suivi_recommande' => 'Suivi recommandé'
];

foreach ($statsByStatut as $stat) {
    $statutLabels[] = $statutNoms[$stat['statut']] ?? $stat['statut'];
    $statutData[] = $stat['total'];
}

// Données par mois (Comptes Rendus)
$moisLabels = [];
$moisData = [];
foreach ($comptesRendusParMois as $item) {
    $moisLabels[] = date('M Y', strtotime($item['mois'] . '-01'));
    $moisData[] = $item['total'];
}

// Données par mois (Consultations)
$consultMoisLabels = [];
$consultMoisData = [];
foreach ($consultationsParMois as $item) {
    $consultMoisLabels[] = date('M Y', strtotime($item['mois'] . '-01'));
    $consultMoisData[] = $item['total'];
}

// Données par motif
$motifLabels = [];
$motifData = [];
foreach ($consultationsParMotif as $item) {
    $motifLabels[] = substr($item['motif_consultation'], 0, 20) . (strlen($item['motif_consultation']) > 20 ? '...' : '');
    $motifData[] = $item['total'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Supportini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --success: #4caf50;
            --warning: #ff9800;
            --info: #2196f3;
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
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .stat-icon.red { background-color: rgba(211, 47, 47, 0.2); color: var(--primary-red); }
        .stat-icon.green { background-color: rgba(76, 175, 80, 0.2); color: var(--success); }
        .stat-icon.orange { background-color: rgba(255, 152, 0, 0.2); color: var(--warning); }
        .stat-icon.blue { background-color: rgba(33, 150, 243, 0.2); color: var(--info); }
        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-info p {
            color: var(--text-muted);
            font-size: 14px;
        }
        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .chart-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .chart-title i {
            color: var(--primary-red);
        }
        /* Tables */
        .table-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background-color: rgba(211, 47, 47, 0.2);
            color: var(--primary-red);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-light);
            font-size: 14px;
        }
        .table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-success { background-color: rgba(76, 175, 80, 0.2); color: var(--success); }
        .badge-warning { background-color: rgba(255, 152, 0, 0.2); color: var(--warning); }
        .badge-info { background-color: rgba(33, 150, 243, 0.2); color: var(--info); }
        .badge-danger { background-color: rgba(211, 47, 47, 0.2); color: var(--primary-red); }
        /* Progress Bar */
        .progress-container {
            margin-top: 15px;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .progress-bar {
            height: 10px;
            background-color: var(--border-color);
            border-radius: 5px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-red), var(--light-red));
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--border-color);
        }
        /* Btn */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: var(--primary-red);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--dark-red);
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../uploads/logoN.png" class="sidebar-logo" alt="Logo">
            <h2 class="sidebar-title">SUPPORTINI<span>.TN</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link active">
                <i class="fa-solid fa-chart-simple"></i> Dashboard
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-regular fa-user"></i> Utilisateurs
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Forum
            </a>
            <a href="consultations.php" class="sidebar-link">
                <i class="fa-solid fa-calendar-check"></i> Rendez-vous
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-calendar"></i> Événements
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-layer-group"></i> Catégories
            </a>
            <a href="CompteRendus.php" class="sidebar-link">
                <i class="fa-solid fa-file-alt"></i> Comptes Rendus
            </a>
            <a href="#" class="sidebar-link">
                <i class="fa-solid fa-exclamation-circle"></i> Réclamation
            </a>
            <a href="/logout.php" class="sidebar-link">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-chart-simple"></i> Dashboard</h1>
        </div>

        <!-- Stats Cards Comptes Rendus -->
        <h2 style="margin-bottom: 20px; color: var(--primary-red);"><i class="fa-solid fa-file-alt"></i> Comptes Rendus</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalComptesRendus ?></h3>
                    <p>Total Comptes Rendus</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count($consultationsSansCompteRendu) ?></h3>
                    <p>Sans Compte Rendu</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-link"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $pourcentageLiaison ?>%</h3>
                    <p>Taux de liaison</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards Rendez-vous -->
        <h2 style="margin: 30px 0 20px 0; color: var(--info);"><i class="fa-solid fa-calendar-check"></i> Rendez-vous</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalConsultations ?></h3>
                    <p>Total Rendez-vous</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $consultationsAujourdhui ?></h3>
                    <p>Aujourd'hui</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $consultationsCetteSemaine ?></h3>
                    <p>Cette semaine</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fa-solid fa-calendar"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $consultationsCeMois ?></h3>
                    <p>Ce mois</p>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-chart-pie"></i> CR par Statut</h3>
                <canvas id="statutChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-chart-bar"></i> RDV par Motif</h3>
                <?php if(empty($motifData)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-chart-bar"></i>
                        <p>Pas de données disponibles</p>
                    </div>
                <?php else: ?>
                    <canvas id="motifChart"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-chart-line"></i> Évolution CR Mensuelle</h3>
                <?php if(empty($moisData)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-chart-area"></i>
                        <p>Pas de données disponibles</p>
                    </div>
                <?php else: ?>
                    <canvas id="evolutionChart"></canvas>
                <?php endif; ?>
            </div>
            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-chart-area"></i> Évolution RDV Mensuelle</h3>
                <?php if(empty($consultMoisData)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-chart-area"></i>
                        <p>Pas de données disponibles</p>
                    </div>
                <?php else: ?>
                    <canvas id="consultEvolutionChart"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Prochains Rendez-vous -->
        <?php if(!empty($prochainesConsultations)): ?>
        <div class="table-card">
            <h3 class="chart-title"><i class="fa-solid fa-clock" style="color: var(--info);"></i> Prochains Rendez-vous</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Motif</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($prochainesConsultations as $consult): ?>
                        <tr>
                            <td>#<?= $consult['id_consultation'] ?></td>
                            <td>
                                <?php if(!empty($consult['date_consultation'])): ?>
                                    <?= date('d/m/Y', strtotime($consult['date_consultation'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($consult['heure_consultation'] ?? '-') ?></td>
                            <td><?= htmlspecialchars(substr($consult['motif_consultation'] ?? '', 0, 40)) ?><?= strlen($consult['motif_consultation'] ?? '') > 40 ? '...' : '' ?></td>
                            <td>
                                <a href="consultations.php?view=<?= $consult['id_consultation'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Statistiques par Statut -->
        <div class="table-card">
            <h3 class="chart-title"><i class="fa-solid fa-chart-bar"></i> Détails par Statut</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th>Nombre</th>
                        <th>Pourcentage</th>
                        <th>Progression</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($statsByStatut as $stat): ?>
                        <?php 
                        $pourcentage = $totalComptesRendus > 0 ? round(($stat['total'] / $totalComptesRendus) * 100, 1) : 0;
                        $badgeClass = match($stat['statut']) {
                            'rendezVous_termine' => 'badge-success',
                            'autre_rendezVous_necessaire' => 'badge-warning',
                            'suivi_recommande' => 'badge-info',
                            default => 'badge-danger'
                        };
                        ?>
                        <tr>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $statutNoms[$stat['statut']] ?? $stat['statut'] ?>
                                </span>
                            </td>
                            <td><strong><?= $stat['total'] ?></strong></td>
                            <td><?= $pourcentage ?>%</td>
                            <td style="width: 200px;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $pourcentage ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Comptes Rendus Récents -->
        <div class="table-card">
            <h3 class="chart-title"><i class="fa-solid fa-clock"></i> Comptes Rendus Récents</h3>
            <?php if(empty($recentComptesRendus)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-file-circle-question"></i>
                    <p>Aucun compte rendu disponible</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Date Consultation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentComptesRendus as $cr): ?>
                            <?php 
                            $badgeClass = match($cr['statut']) {
                                'rendezVous_termine' => 'badge-success',
                                'autre_rendezVous_necessaire' => 'badge-warning',
                                'suivi_recommande' => 'badge-info',
                                default => 'badge-danger'
                            };
                            ?>
                            <tr>
                                <td>#<?= $cr['id_compte_rendu'] ?></td>
                                <td><?= htmlspecialchars($cr['nom']) ?></td>
                                <td><?= htmlspecialchars($cr['email']) ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= $statutNoms[$cr['statut']] ?? $cr['statut'] ?></span></td>
                                <td>
                                    <?php if(!empty($cr['date_consultation'])): ?>
                                        <?= date('d/m/Y', strtotime($cr['date_consultation'])) ?>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="CompteRendus.php?edit=<?= $cr['id_compte_rendu'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Consultations sans compte rendu -->
        <?php if(!empty($consultationsSansCompteRendu)): ?>
        <div class="table-card">
            <h3 class="chart-title"><i class="fa-solid fa-exclamation-triangle" style="color: var(--warning);"></i> Consultations en attente de compte rendu</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Motif</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach(array_slice($consultationsSansCompteRendu, 0, 5) as $consultation): ?>
                        <tr>
                            <td>#<?= $consultation['id_consultation'] ?></td>
                            <td>
                                <?php if(!empty($consultation['date_consultation'])): ?>
                                    <?= date('d/m/Y', strtotime($consultation['date_consultation'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(substr($consultation['motif_consultation'] ?? '', 0, 50)) ?>...</td>
                            <td>
                                <a href="CompteRendus.php?consultation=<?= $consultation['id_consultation'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-plus"></i> Créer CR
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if(count($consultationsSansCompteRendu) > 5): ?>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="consultations.php" class="btn btn-primary">
                        Voir toutes les consultations (<?= count($consultationsSansCompteRendu) ?>)
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Graphique Statut (Doughnut)
        const statutCtx = document.getElementById('statutChart');
        if (statutCtx) {
            new Chart(statutCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($statutLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($statutData) ?>,
                        backgroundColor: ['#4caf50', '#ff9800', '#2196f3'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#f5f5f5',
                                padding: 20,
                                font: { size: 13 }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // Graphique Evolution (Line)
        <?php if(!empty($moisData)): ?>
        const evolutionCtx = document.getElementById('evolutionChart');
        if (evolutionCtx) {
            new Chart(evolutionCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($moisLabels) ?>,
                    datasets: [{
                        label: 'Comptes Rendus',
                        data: <?= json_encode($moisData) ?>,
                        borderColor: '#d32f2f',
                        backgroundColor: 'rgba(211, 47, 47, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#d32f2f',
                        pointRadius: 5,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#aaaaaa' },
                            grid: { color: '#333333' }
                        },
                        x: {
                            ticks: { color: '#aaaaaa' },
                            grid: { color: '#333333' }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // Graphique Motif (Bar)
        <?php if(!empty($motifData)): ?>
        const motifCtx = document.getElementById('motifChart');
        if (motifCtx) {
            new Chart(motifCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($motifLabels) ?>,
                    datasets: [{
                        label: 'Nombre de RDV',
                        data: <?= json_encode($motifData) ?>,
                        backgroundColor: [
                            'rgba(211, 47, 47, 0.7)',
                            'rgba(33, 150, 243, 0.7)',
                            'rgba(76, 175, 80, 0.7)',
                            'rgba(255, 152, 0, 0.7)',
                            'rgba(156, 39, 176, 0.7)',
                            'rgba(0, 188, 212, 0.7)',
                            'rgba(255, 87, 34, 0.7)',
                            'rgba(103, 58, 183, 0.7)',
                            'rgba(233, 30, 99, 0.7)',
                            'rgba(63, 81, 181, 0.7)'
                        ],
                        borderWidth: 0,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { color: '#aaaaaa' },
                            grid: { color: '#333333' }
                        },
                        y: {
                            ticks: { color: '#aaaaaa' },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // Graphique Evolution Consultations (Line)
        <?php if(!empty($consultMoisData)): ?>
        const consultEvolutionCtx = document.getElementById('consultEvolutionChart');
        if (consultEvolutionCtx) {
            new Chart(consultEvolutionCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($consultMoisLabels) ?>,
                    datasets: [{
                        label: 'Rendez-vous',
                        data: <?= json_encode($consultMoisData) ?>,
                        borderColor: '#2196f3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#2196f3',
                        pointRadius: 5,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#aaaaaa' },
                            grid: { color: '#333333' }
                        },
                        x: {
                            ticks: { color: '#aaaaaa' },
                            grid: { color: '#333333' }
                        }
                    }
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>
