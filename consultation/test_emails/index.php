<?php
/**
 * Page de test pour visualiser les emails sauvegardés en mode localhost
 */
$emailDir = __DIR__;
$emails = [];

if (is_dir($emailDir)) {
    $files = scandir($emailDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $emails[] = [
                'filename' => $file,
                'path' => $emailDir . '/' . $file,
                'date' => filemtime($emailDir . '/' . $file),
                'size' => filesize($emailDir . '/' . $file)
            ];
        }
    }
    // Trier par date (plus récent en premier)
    usort($emails, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emails de Test - Supportini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-red: #d32f2f;
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
            font-family: Arial, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #b71c1c, #d32f2f);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            color: white;
        }
        .info-box {
            background: rgba(255, 152, 0, 0.1);
            border: 2px solid #ff9800;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            color: #ffcc80;
        }
        .email-list {
            display: grid;
            gap: 20px;
        }
        .email-item {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid var(--primary-red);
        }
        .email-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .email-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: var(--primary-red);
            color: white;
        }
        .btn-primary:hover {
            background: #b71c1c;
        }
        .btn-danger {
            background: transparent;
            border: 1px solid var(--primary-red);
            color: var(--primary-red);
        }
        .email-meta {
            color: var(--text-muted);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa-solid fa-envelope"></i> Emails de Test</h1>
            <p>Emails sauvegardés en mode localhost</p>
        </div>

        <div class="info-box">
            <strong><i class="fa-solid fa-info-circle"></i> Mode Test Localhost</strong>
            <p style="margin-top: 10px;">
                En mode localhost, les emails ne sont pas réellement envoyés mais sauvegardés dans ce dossier pour vous permettre de les visualiser et tester.
            </p>
            <p style="margin-top: 10px;">
                <a href="../view/backoffice/CompteRendus.php" style="color: #ffcc80; text-decoration: underline;">
                    <i class="fa-solid fa-arrow-left"></i> Retour à la gestion
                </a>
            </p>
        </div>

        <h2 style="margin-bottom: 20px;">
            <?= count($emails) ?> email(s) sauvegardé(s)
        </h2>

        <?php if (empty($emails)): ?>
            <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                <i class="fa-solid fa-inbox" style="font-size: 64px; margin-bottom: 20px; display: block;"></i>
                <p>Aucun email sauvegardé pour le moment.</p>
                <p style="margin-top: 10px;">Les emails seront sauvegardés ici lorsque vous cocherez "Envoyer un email" dans les formulaires.</p>
            </div>
        <?php else: ?>
            <div class="email-list">
                <?php foreach ($emails as $email): ?>
                    <div class="email-item">
                        <div class="email-header">
                            <div>
                                <h3 style="color: var(--text-light); margin-bottom: 5px;">
                                    <?= htmlspecialchars($email['filename']) ?>
                                </h3>
                                <div class="email-meta">
                                    <i class="fa-solid fa-calendar"></i> 
                                    <?= date('d/m/Y H:i:s', $email['date']) ?> | 
                                    <i class="fa-solid fa-file"></i> 
                                    <?= round($email['size'] / 1024, 2) ?> KB
                                </div>
                            </div>
                            <div class="email-actions">
                                <a href="<?= htmlspecialchars($email['filename']) ?>" target="_blank" class="btn btn-primary">
                                    <i class="fa-solid fa-eye"></i> Voir
                                </a>
                                <a href="?delete=<?= urlencode($email['filename']) ?>" 
                                   onclick="return confirm('Supprimer cet email de test ?')" 
                                   class="btn btn-danger">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Supprimer un email
    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $fileToDelete = __DIR__ . '/' . basename($_GET['delete']);
        if (file_exists($fileToDelete) && pathinfo($fileToDelete, PATHINFO_EXTENSION) === 'html') {
            @unlink($fileToDelete);
            header('Location: index.php');
            exit;
        }
    }
    ?>
</body>
</html>

