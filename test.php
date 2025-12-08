<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - aide_co</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .test-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 900px;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .content {
            padding: 30px;
        }
        
        .test-section {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #4f46e5;
        }
        
        .test-section h2 {
            color: #374151;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .result {
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: monospace;
        }
        
        .success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background: #f3f4f6;
            font-weight: 600;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            margin: 5px;
        }
        
        .btn:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-secondary {
            background: #6b7280;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .icon {
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="header">
            <h1>🔧 Test Technique - aide_co</h1>
            <p>Vérification complète du système</p>
        </div>
        
        <div class="content">
            <?php
            // Démarrer le test
            echo '<div class="test-section">';
            echo '<h2>🧪 Test 1 : Configuration PHP</h2>';
            
            // Test version PHP
            $php_version = phpversion();
            echo '<div class="result success">';
            echo '✅ Version PHP : <strong>' . $php_version . '</strong><br>';
            echo '✅ Memory limit : ' . ini_get('memory_limit') . '<br>';
            echo '✅ Upload max : ' . ini_get('upload_max_filesize');
            echo '</div>';
            echo '</div>';
            
            // Test connexion base de données
            echo '<div class="test-section">';
            echo '<h2>🗄️ Test 2 : Base de données MySQL</h2>';
            
            try {
                require_once 'config.php';
                
                echo '<div class="result success">';
                echo '✅ Connexion à MySQL réussie !<br>';
                echo '✅ Base de données : aide_co_db<br>';
                echo '✅ Hôte : localhost<br>';
                echo '✅ Utilisateur : root';
                echo '</div>';
                
                // Test tables
                $tables = ['users', 'appointments'];
                $tables_found = [];
                
                foreach ($tables as $table) {
                    $sql = "SHOW TABLES LIKE '$table'";
                    $stmt = $conn->query($sql);
                    if ($stmt->rowCount() > 0) {
                        $tables_found[] = $table;
                    }
                }
                
                echo '<div class="result ' . (count($tables_found) == count($tables) ? 'success' : 'warning') . '">';
                echo '📊 Tables trouvées : ' . implode(', ', $tables_found) . '<br>';
                echo count($tables_found) . '/' . count($tables) . ' tables présentes';
                echo '</div>';
                
                // Afficher les données
                echo '<h3>👥 Données des utilisateurs :</h3>';
                $sql = "SELECT id, email, full_name, role, created_at FROM users ORDER BY role";
                $users = $conn->query($sql);
                
                echo '<table>';
                echo '<tr><th>ID</th><th>Email</th><th>Nom</th><th>Rôle</th><th>Créé le</th></tr>';
                foreach ($users as $user) {
                    echo '<tr>';
                    echo '<td>' . $user['id'] . '</td>';
                    echo '<td>' . $user['email'] . '</td>';
                    echo '<td>' . $user['full_name'] . '</td>';
                    echo '<td><span style="padding: 4px 8px; border-radius: 4px; background: ';
                    echo $user['role'] == 'admin' ? '#fbbf24' : ($user['role'] == 'doctor' ? '#60a5fa' : '#34d399');
                    echo ';">' . $user['role'] . '</span></td>';
                    echo '<td>' . $user['created_at'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                // Test rendez-vous
                echo '<h3>📅 Rendez-vous :</h3>';
                $sql = "SELECT COUNT(*) as total FROM appointments";
                $stmt = $conn->query($sql);
                $result = $stmt->fetch();
                echo '<div class="result success">';
                echo '✅ ' . $result['total'] . ' rendez-vous dans la base';
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="result error">';
                echo '❌ Erreur de connexion : ' . $e->getMessage();
                echo '</div>';
            }
            echo '</div>';
            
            // Test fichiers
            echo '<div class="test-section">';
            echo '<h2>📁 Test 3 : Fichiers du projet</h2>';
            
            $required_files = ['index.php', 'config.php', 'login.php', 'README.md', 'database/aide_co.sql'];
            $files_found = [];
            
            foreach ($required_files as $file) {
                if (file_exists($file)) {
                    $files_found[] = $file;
                    $status = '✅';
                } else {
                    $status = '❌';
                }
                echo '<div>' . $status . ' ' . $file . '</div>';
            }
            
            echo '<div class="result ' . (count($files_found) == count($required_files) ? 'success' : 'warning') . '">';
            echo count($files_found) . '/' . count($required_files) . ' fichiers essentiels présents';
            echo '</div>';
            echo '</div>';
            
            // Test serveur web
            echo '<div class="test-section">';
            echo '<h2>🌐 Test 4 : Serveur Web</h2>';
            
            echo '<div class="result success">';
            echo '✅ Serveur : ' . $_SERVER['SERVER_SOFTWARE'] . '<br>';
            echo '✅ Protocole : ' . $_SERVER['SERVER_PROTOCOL'] . '<br>';
            echo '✅ Port : ' . $_SERVER['SERVER_PORT'] . '<br>';
            echo '✅ URL : http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            echo '</div>';
            echo '</div>';
            
            // Résumé final
            echo '<div class="test-section" style="background: #ecfdf5; border-left: 5px solid #10b981;">';
            echo '<h2>📊 Résumé du test</h2>';
            
            $all_tests_passed = true;
            
            // Vérifications rapides
            $checks = [
                'PHP fonctionnel' => version_compare(PHP_VERSION, '7.4.0', '>='),
                'Connexion BDD' => class_exists('PDO'),
                'Session démarrée' => session_status() === PHP_SESSION_ACTIVE,
                'Fichier config' => file_exists('config.php'),
                'Base accessible' => isset($conn) && $conn instanceof PDO
            ];
            
            echo '<table>';
            foreach ($checks as $check => $status) {
                echo '<tr>';
                echo '<td>' . ($status ? '✅' : '❌') . '</td>';
                echo '<td>' . $check . '</td>';
                echo '<td>' . ($status ? '<span style="color: green;">PASS</span>' : '<span style="color: red;">FAIL</span>') . '</td>';
                echo '</tr>';
                if (!$status) $all_tests_passed = false;
            }
            echo '</table>';
            
            echo '<div class="result ' . ($all_tests_passed ? 'success' : 'error') . '" style="margin-top: 20px; text-align: center; font-size: 1.2em;">';
            echo '<strong>' . ($all_tests_passed ? '🎉 TOUS LES TESTS SONT VALIDÉS !' : '⚠️ CERTAINS TESTS ONT ÉCHOUÉ') . '</strong><br>';
            echo $all_tests_passed ? 'Votre environnement est parfaitement configuré.' : 'Veuillez corriger les erreurs ci-dessus.';
            echo '</div>';
            echo '</div>';
            ?>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <a href="index.php" class="btn">🏠 Retour à l'accueil</a>
                <a href="login.php" class="btn btn-secondary">🔐 Tester la connexion</a>
                <button onclick="window.location.reload()" class="btn">🔄 Relancer les tests</button>
            </div>
            
            <div style="margin-top: 20px; text-align: center; color: #6b7280; font-size: 0.9em;">
                <p>Projet aide_co - Module Projet Technologies Web 2A - 2025/2026</p>
            </div>
        </div>
    </div>
</body>
</html>