<?php
// Paramètres de connexion à la base de données
$host = 'localhost';         
$db   = 'gestion_consultations'; 
$user = 'root';            
$pass = '';                   
$charset = 'utf8mb4';         

// DSN pour PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // gestion des erreurs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch associatif par défaut
    PDO::ATTR_EMULATE_PREPARES   => false,                  // désactive l'émulation des requêtes préparées
];

// Création de l'objet PDO
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
