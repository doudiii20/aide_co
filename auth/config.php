<?php
class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (self::$pdo == null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=evenements;charset=utf8',
                    'root',
                    ''
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                die('Erreur : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>
