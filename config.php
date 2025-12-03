<?php
class Config
{
    public static function getConnexion()
    {
        // Paramètres de connexion
        $host   = "localhost";
        $dbname = "psy_forum";
        $user   = "root";
        $pass   = "";

        // Création de l'objet PDO (connexion à MySQL)
        $pdo = new PDO(
            "mysql:host=" . $host . ";dbname=" . $dbname . ";port=3306",
            $user,
            $pass
        );

        // Option PDO : remonter les erreurs sous forme d'exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // On renvoie la connexion
        return $pdo;
    }
}
?>
