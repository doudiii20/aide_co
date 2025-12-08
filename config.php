<?php
// ============================================
// CONFIGURATION DE LA BASE DE DONNÉES aide_co
// ============================================

// Paramètres de connexion
$host = 'localhost';        // Serveur MySQL (XAMPP)
$dbname = 'aide_co_db';     // Nom de ta base de données
$username = 'root';         // Utilisateur MySQL (par défaut XAMPP)
$password = '';             // Mot de passe (vide par défaut dans XAMPP)

// Options de connexion PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    // Activer les exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES => false,            // Désactiver l'émulation
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // Encodage UTF-8
];

try {
    // Créer la connexion PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
    // Message de débogage (à commenter en production)
    // error_log("✅ Connexion à la base de données réussie : $dbname");
    
} catch (PDOException $e) {
    // En cas d'erreur
    $error_message = "❌ ERREUR DE CONNEXION à la base de données : " . $e->getMessage();
    error_log($error_message);
    
    // Affichage en mode développement
    if (php_sapi_name() !== 'cli') { // Si pas en ligne de commande
        echo '<div style="background: #fee; border: 2px solid #c33; padding: 20px; margin: 20px; border-radius: 10px;">';
        echo '<h3 style="color: #c33;">Erreur de connexion base de données</h3>';
        echo '<p><strong>Message :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>Code :</strong> ' . $e->getCode() . '</p>';
        echo '<p><strong>Fichier :</strong> ' . $e->getFile() . '</p>';
        echo '<p><strong>Ligne :</strong> ' . $e->getLine() . '</p>';
        echo '<hr>';
        echo '<h4>Vérifications à faire :</h4>';
        echo '<ol>';
        echo '<li>Vérifiez que MySQL est démarré dans XAMPP</li>';
        echo '<li>Vérifiez que la base "' . $dbname . '" existe dans phpMyAdmin</li>';
        echo '<li>Vérifiez le mot de passe MySQL (vide par défaut dans XAMPP)</li>';
        echo '<li>Vérifiez que le fichier config.php est dans le bon dossier</li>';
        echo '</ol>';
        echo '<p><a href="http://localhost/phpmyadmin/" target="_blank">→ Ouvrir phpMyAdmin</a></p>';
        echo '</div>';
    }
    
    // Arrêter l'exécution
    die();
}

// ============================================
// FONCTIONS UTILES
// ============================================

/**
 * Sécurise une chaîne pour l'affichage HTML
 */
function safe($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie le rôle de l'utilisateur
 */
function checkRole($required_role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        redirect('login.php');
    }
}

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>