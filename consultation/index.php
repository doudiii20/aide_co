<?php
require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/model/Consultation.php';
require_once __DIR__ . '/model/CompteRendu.php';
require_once __DIR__ . '/controller/ConsultationController.php';
require_once __DIR__ . '/controller/CompteRenduController.php';

// Initialiser les controllers
$consultationController = new RendezVousController($pdo);
$compteRenduController = new CompteRenduController($pdo);

// Récupérer les statistiques
$totalConsultations = count($consultationController->getAllRendezVous());
$totalComptesRendus = $compteRenduController->getTotalComptesRendus();
$recentConsultations = $consultationController->getAllRendezVous();
$recentConsultations = array_slice($recentConsultations, 0, 5);
$recentComptesRendus = $compteRenduController->getRecentComptesRendus(5);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Consultations & Comptes Rendus | Supportini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        /* Header */
        .header {
            background: linear-gradient(135deg, var(--dark-red), var(--primary-red));
            padding: 30px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 40px;
        }
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        .site-title {
            font-size: 32px;
            font-weight: 700;
            color: white;
        }
        .site-title span {
            color: #ffccbc;
        }
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--card-bg), #252525);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            border-left: 5px solid var(--primary-red);
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--primary-red);
        }
        .stat-value {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-light);
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 500;
        }
        /* Main Sections */
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        .section-card {
            background-color: var(--card-bg);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }
        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .section-title i {
            color: var(--primary-red);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background-color: var(--primary-red);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.4);
        }
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary-red);
            color: var(--primary-red);
        }
        .btn-outline:hover {
            background-color: var(--primary-red);
            color: white;
        }
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        .btn-success:hover {
            background-color: #388e3c;
        }
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        .btn-info:hover {
            background-color: #1976d2;
        }
        /* Items List */
        .items-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .item-card {
            background-color: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid var(--primary-red);
            transition: all 0.3s ease;
        }
        .item-card:hover {
            background-color: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .item-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-light);
        }
        .item-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background-color: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }
        .item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            color: var(--text-muted);
            font-size: 14px;
        }
        .item-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .item-meta-item i {
            color: var(--primary-red);
        }
        .item-actions {
            display: flex;
            gap: 10px;
        }
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }
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
        .empty-state p {
            font-size: 16px;
        }
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .action-card {
            background: linear-gradient(135deg, var(--card-bg), #252525);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            color: var(--text-light);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .action-card:hover {
            border-color: var(--primary-red);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(211, 47, 47, 0.3);
        }
        .action-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--primary-red);
        }
        .action-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .action-desc {
            font-size: 13px;
            color: var(--text-muted);
        }
        @media (max-width: 768px) {
            .sections-grid {
                grid-template-columns: 1fr;
            }
            .header-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="uploads/logoN.png" class="logo" alt="Logo Supportini" onerror="this.style.display='none'">
                <h1 class="site-title">SUPPORTINI<span>.TN</span></h1>
            </div>
            <div style="color: white; text-align: right;">
                <div style="font-size: 14px; opacity: 0.9;">Plateforme de gestion</div>
                <div style="font-size: 18px; font-weight: 600;">Consultations & Comptes Rendus</div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="stat-value"><?= $totalConsultations ?></div>
                <div class="stat-label">Total Consultations</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
                <div class="stat-value"><?= $totalComptesRendus ?></div>
                <div class="stat-label">Total Comptes Rendus</div>
            </div>
        </div>

        <!-- Bouton principal pour Consultation -->
        <div style="text-align: center; margin: 40px 0; padding: 30px; background: linear-gradient(135deg, var(--card-bg), #252525); border-radius: 15px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3); border: 2px solid var(--primary-red);">
            <h2 style="color: var(--text-light); margin-bottom: 20px; font-size: 24px;">
                <i class="fa-solid fa-calendar-check" style="color: var(--primary-red);"></i>
                Gestion des Consultations
            </h2>
            <p style="color: var(--text-muted); margin-bottom: 25px; font-size: 16px;">
                Accédez rapidement à la gestion complète des consultations et rendez-vous
            </p>
            <a href="view/backoffice/consultations.php" class="btn btn-primary" style="font-size: 18px; padding: 18px 50px; display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Accéder aux Consultations</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="view/backoffice/consultations.php" class="action-card">
                <div class="action-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="action-title">Gérer Consultations</div>
                <div class="action-desc">Backoffice</div>
            </a>
            <a href="view/backoffice/CompteRendus.php" class="action-card">
                <div class="action-icon"><i class="fa-solid fa-file-alt"></i></div>
                <div class="action-title">Gérer Comptes Rendus</div>
                <div class="action-desc">Backoffice</div>
            </a>
            <a href="view/backoffice/dashboard.php" class="action-card">
                <div class="action-icon"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="action-title">Dashboard</div>
                <div class="action-desc">Statistiques complètes</div>
            </a>
            <a href="view/frontoffice/consultations.php" class="action-card">
                <div class="action-icon"><i class="fa-solid fa-calendar-plus"></i></div>
                <div class="action-title">Prendre Rendez-vous</div>
                <div class="action-desc">Interface publique</div>
            </a>
            <a href="view/frontoffice/CompteRendus.php" class="action-card">
                <div class="action-icon"><i class="fa-solid fa-file-medical"></i></div>
                <div class="action-title">Mes Comptes Rendus</div>
                <div class="action-desc">Interface publique</div>
            </a>
        </div>

        <!-- Recent Consultations & Comptes Rendus -->
        <div class="sections-grid">
            <!-- Consultations Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fa-solid fa-calendar-check"></i>
                        Consultations Récentes
                    </h2>
                    <a href="view/backoffice/consultations.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-eye"></i> Voir tout
                    </a>
                </div>
                <div class="items-list">
                    <?php if(empty($recentConsultations)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>Aucune consultation disponible</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($recentConsultations as $consultation): ?>
                            <div class="item-card">
                                <div class="item-header">
                                    <div class="item-title"><?= htmlspecialchars($consultation['nom_complet']) ?></div>
                                    <span class="item-badge badge-success">
                                        <?= htmlspecialchars($consultation['type_rendezVous']) ?>
                                    </span>
                                </div>
                                <div class="item-meta">
                                    <div class="item-meta-item">
                                        <i class="fa-solid fa-envelope"></i>
                                        <span><?= htmlspecialchars($consultation['email_universitaire']) ?></span>
                                    </div>
                                    <div class="item-meta-item">
                                        <i class="fa-solid fa-calendar"></i>
                                        <span><?= htmlspecialchars($consultation['date_souhaitee']) ?></span>
                                    </div>
                                    <div class="item-meta-item">
                                        <i class="fa-solid fa-clock"></i>
                                        <span><?= htmlspecialchars($consultation['heure']) ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="view/backoffice/consultations.php?edit=<?= $consultation['id_rendezVous'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <a href="view/backoffice/CompteRendus.php?consultation=<?= $consultation['id_rendezVous'] ?>" class="btn btn-success btn-sm">
                                        <i class="fa-solid fa-file-plus"></i> Créer CR
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comptes Rendus Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fa-solid fa-file-alt"></i>
                        Comptes Rendus Récents
                    </h2>
                    <a href="view/backoffice/CompteRendus.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-eye"></i> Voir tout
                    </a>
                </div>
                <div class="items-list">
                    <?php if(empty($recentComptesRendus)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-file-circle-question"></i>
                            <p>Aucun compte rendu disponible</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($recentComptesRendus as $cr): ?>
                            <div class="item-card">
                                <div class="item-header">
                                    <div class="item-title"><?= htmlspecialchars($cr['nom']) ?></div>
                                    <span class="item-badge badge-success">
                                        <?= htmlspecialchars($cr['statut']) ?>
                                    </span>
                                </div>
                                <div class="item-meta">
                                    <div class="item-meta-item">
                                        <i class="fa-solid fa-envelope"></i>
                                        <span><?= htmlspecialchars($cr['email']) ?></span>
                                    </div>
                                    <?php if(!empty($cr['date_consultation'])): ?>
                                        <div class="item-meta-item">
                                            <i class="fa-solid fa-calendar"></i>
                                            <span><?= date('d/m/Y', strtotime($cr['date_consultation'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(!empty($cr['id_consultation']) || !empty($cr['consultation_id'])): ?>
                                        <div class="item-meta-item">
                                            <i class="fa-solid fa-link"></i>
                                            <span>Consultation #<?= $cr['id_consultation'] ?? $cr['consultation_id'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-actions">
                                    <a href="view/backoffice/CompteRendus.php?edit=<?= $cr['id_compte_rendu'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <a href="view/frontoffice/generate_pdf.php?id=<?= $cr['id_compte_rendu'] ?>" class="btn btn-info btn-sm" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

