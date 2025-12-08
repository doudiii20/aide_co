<?php
require_once __DIR__ . '/../../auth/config.php';
require_once __DIR__ . '/../../model/CompteRendu.php';
require_once __DIR__ . '/../../controller/CompteRenduController.php';

$controller = new CompteRenduController($pdo);

// Récupération des filtres
$nameFilter = $_GET['name'] ?? '';
$statutFilter = $_GET['statut'] ?? '';

// Récupération de tous les comptes rendus
$comptesRendus = $controller->getAllComptesRendus();

// Filtrage des résultats
if ($nameFilter) {
    $comptesRendus = array_filter($comptesRendus, function($c) use ($nameFilter) {
        return stripos($c['nom'], $nameFilter) !== false || 
               stripos($c['email'], $nameFilter) !== false ||
               stripos($c['description'], $nameFilter) !== false;
    });
}

if ($statutFilter) {
    $comptesRendus = array_filter($comptesRendus, function($c) use ($statutFilter) {
        return $c['statut'] === $statutFilter;
    });
}

// Réindexer le tableau après filtrage
$comptesRendus = array_values($comptesRendus);

// Calcul des statistiques
$totalComptesRendus = count($controller->getAllComptesRendus());
$statutTermine = count(array_filter($controller->getAllComptesRendus(), function($c) {
    return $c['statut'] === 'rendezVous_termine';
}));
$statutAutre = count(array_filter($controller->getAllComptesRendus(), function($c) {
    return $c['statut'] === 'autre_rendezVous_necessaire';
}));
$statutSuivi = count(array_filter($controller->getAllComptesRendus(), function($c) {
    return $c['statut'] === 'suivi_recommande';
}));

// Fonction pour formater le statut
function formatStatut($statut) {
    switch($statut) {
        case 'rendezVous_termine':
            return 'Rendez-vous terminé';
        case 'autre_rendezVous_necessaire':
            return 'Autre rendez-vous nécessaire';
        case 'suivi_recommande':
            return 'Suivi recommandé';
        default:
            return $statut;
    }
}

// Fonction pour obtenir la classe CSS du statut
function getStatutClass($statut) {
    switch($statut) {
        case 'rendezVous_termine':
            return 'badge-available';
        case 'autre_rendezVous_necessaire':
            return 'badge-full';
        case 'suivi_recommande':
            return 'badge-available';
        default:
            return 'badge-full';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Comptes Rendus | Supportini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #d32f2f;
            --dark-primary: #b71c1c;
            --light-primary: #ff6659;
            --dark-bg: #121212;
            --card-bg: #1e1e1e;
            --text-light: #f5f5f5;
            --text-muted: #aaaaaa;
            --border-color: #333333;
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
        /* Header */
        .main-header {
            background: linear-gradient(135deg, var(--dark-primary), var(--primary-color));
            padding: 25px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            font-size: 28px;
            font-weight: 700;
            color: white;
        }
        .site-title span {
            color: #ffccbc;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-title {
            font-size: 36px;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        .page-title i {
            color: var(--primary-color);
            margin-right: 15px;
        }
        .page-subtitle {
            color: var(--text-muted);
            font-size: 18px;
        }
        /* Search and Filters */
        .search-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 40px;
        }
        .search-title {
            font-size: 22px;
            margin-bottom: 25px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-label {
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-light);
            font-size: 14px;
        }
        .form-control {
            padding: 14px 16px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.2);
        }
        .form-actions {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--dark-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.4);
        }
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-light);
        }
        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--card-bg), #252525);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-left: 4px solid var(--primary-color);
        }
        .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-light);
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }
        /* Items Grid */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        .item-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        .item-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            border-color: var(--primary-color);
        }
        .item-image-container {
            position: relative;
            overflow: hidden;
            height: 220px;
            background: linear-gradient(135deg, var(--primary-color), var(--dark-primary));
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .item-icon {
            font-size: 80px;
            color: rgba(255, 255, 255, 0.3);
        }
        .item-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .badge-available {
            background-color: #4caf50;
        }
        .badge-full {
            background-color: var(--primary-color);
        }
        .item-content {
            padding: 25px;
        }
        .item-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-light);
            line-height: 1.3;
        }
        .item-meta {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        .item-meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 14px;
        }
        .item-meta-item i {
            color: var(--primary-color);
            width: 16px;
            font-size: 16px;
        }
        .item-description {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .item-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 6px;
        }
        .info-label {
            font-size: 14px;
            color: var(--text-muted);
        }
        .info-value {
            font-weight: 600;
            font-size: 16px;
            color: var(--text-light);
        }
        .highlight {
            color: var(--primary-color);
            font-weight: 700;
        }
        .item-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-details {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-details:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            grid-column: 1 / -1;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--border-color);
        }
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--text-light);
        }
        .empty-state p {
            font-size: 16px;
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        /* Footer */
        .main-footer {
            background-color: var(--card-bg);
            padding: 30px 0;
            margin-top: 60px;
            border-top: 1px solid var(--border-color);
        }
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        .footer-text {
            color: var(--text-muted);
            font-size: 14px;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            .page-title {
                font-size: 28px;
            }
            .search-form {
                grid-template-columns: 1fr;
            }
            .form-actions {
                flex-direction: column;
            }
            .items-grid {
                grid-template-columns: 1fr;
            }
            .item-actions {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            .btn-details {
                justify-content: center;
            }
        }
        @media (max-width: 480px) {
            .main-header {
                padding: 20px 0;
            }
            .logo-section {
                flex-direction: column;
                gap: 10px;
            }
            .site-title {
                font-size: 24px;
            }
            .nav-links {
                flex-direction: column;
                width: 100%;
            }
            .nav-link {
                text-align: center;
            }
            .page-title {
                font-size: 24px;
            }
            .search-container {
                padding: 20px;
            }
            .stats-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="../../uploads/logoN.png" class="logo" alt="Logo Supportini" onerror="this.style.display='none'">
                <h1 class="site-title">SUPPORTINI<span>.TN</span></h1>
            </div>
            
            <nav class="nav-links">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-house"></i> Accueil
                </a>
                <a href="CompteRendus.php" class="nav-link active">
                    <i class="fa-solid fa-file-alt"></i> Comptes Rendus
                </a>
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-calendar-check"></i> Rendez-vous
                </a>
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-user"></i> Mon Compte
                </a>
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-file-alt"></i> Mes Comptes Rendus</h1>
            <p class="page-subtitle">Consultez tous vos comptes rendus de consultation</p>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
                <div class="stat-value"><?= $totalComptesRendus ?></div>
                <div class="stat-label">Comptes rendus total</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-value"><?= $statutTermine ?></div>
                <div class="stat-label">Rendez-vous terminés</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <div class="stat-value"><?= $statutAutre ?></div>
                <div class="stat-label">Autre rendez-vous nécessaire</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div class="stat-value"><?= $statutSuivi ?></div>
                <div class="stat-label">Suivi recommandé</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-container">
            <h2 class="search-title"><i class="fa-solid fa-filter"></i> Filtrer les comptes rendus</h2>
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label class="form-label" for="name">Rechercher</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           placeholder="Nom, email ou description..." 
                           value="<?= htmlspecialchars($nameFilter) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="statut">Statut</label>
                    <select id="statut" name="statut" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="rendezVous_termine" <?= $statutFilter === 'rendezVous_termine' ? 'selected' : '' ?>>Rendez-vous terminé</option>
                        <option value="autre_rendezVous_necessaire" <?= $statutFilter === 'autre_rendezVous_necessaire' ? 'selected' : '' ?>>Autre rendez-vous nécessaire</option>
                        <option value="suivi_recommande" <?= $statutFilter === 'suivi_recommande' ? 'selected' : '' ?>>Suivi recommandé</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Appliquer les filtres
                    </button>
                    <a href="CompteRendus.php" class="btn btn-outline">
                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Items Grid -->
        <div class="items-grid">
            <?php if(empty($comptesRendus)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-file-circle-question"></i>
                    <h3>Aucun compte rendu trouvé</h3>
                    <p><?= $nameFilter || $statutFilter ? 'Aucun compte rendu ne correspond à vos critères de recherche.' : 'Aucun compte rendu disponible pour le moment.' ?></p>
                </div>
            <?php else: ?>
                <?php foreach($comptesRendus as $c): ?>
                    <div class="item-card">
                        <div class="item-image-container">
                            <i class="fa-solid fa-file-medical item-icon"></i>
                            <span class="item-badge <?= getStatutClass($c['statut']) ?>">
                                <i class="fa-solid fa-check"></i> <?= formatStatut($c['statut']) ?>
                            </span>
                        </div>
                        
                        <div class="item-content">
                            <h3 class="item-title"><?= htmlspecialchars($c['nom']) ?></h3>
                            
                            <div class="item-meta">
                                <?php if(!empty($c['id_consultation']) || !empty($c['consultation_id'])): ?>
                                    <div class="item-meta-item">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        <span>
                                            <a href="../backoffice/consultations.php?view=<?= $c['id_consultation'] ?? $c['consultation_id'] ?>" 
                                               style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                                                Consultation #<?= $c['id_consultation'] ?? $c['consultation_id'] ?>
                                            </a>
                                            <?php if(isset($c['date_consultation'])): ?>
                                                - <?= date('d/m/Y', strtotime($c['date_consultation'])) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <div class="item-meta-item">
                                    <i class="fa-regular fa-envelope"></i>
                                    <span><?= htmlspecialchars($c['email']) ?></span>
                                </div>
                                <div class="item-meta-item">
                                    <i class="fa-solid fa-hashtag"></i>
                                    <span>ID: <?= htmlspecialchars($c['id_compte_rendu']) ?></span>
                                </div>
                            </div>
                            
                            <div class="item-description">
                                <?= htmlspecialchars($c['description']) ?>
                            </div>
                            
                            <div class="item-info">
                                <span class="info-label">Statut :</span>
                                <span class="info-value highlight"><?= formatStatut($c['statut']) ?></span>
                            </div>
                            
                            <div class="item-actions">
                                <a class="btn-details" href="#" onclick="alert('Description complète:\n\n<?= addslashes(htmlspecialchars($c['description'])) ?>'); return false;">
                                    <i class="fa-regular fa-eye"></i> Voir détails
                                </a>
                                <a class="btn-details" href="generate_pdf.php?id=<?= $c['id_compte_rendu'] ?>" target="_blank" style="background-color: #4caf50; color: white;">
                                    <i class="fa-solid fa-file-pdf"></i> Télécharger PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2024 Supportini - Tous droits réservés</p>
        </div>
    </footer>

    <script>
        // Gestion des erreurs d'images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.logo');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>
