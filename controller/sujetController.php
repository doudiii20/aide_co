<?php
// controller/sujetController.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/..//model/Sujet.php';

class SujetCRUD
{
    private $pdo;

    public function __construct()
    {
        // Connexion via la classe Config
        $this->pdo = Config::getConnexion();
    }

    // Ajouter un sujet
    public function add($sujet)
    {
        $sql = "INSERT INTO sujet (titre, categorie) VALUES (:titre, :categorie)";
        $req = $this->pdo->prepare($sql);

        $req->bindValue(':titre', $sujet->getTitre());
        $req->bindValue(':categorie', $sujet->getCategorie());

        $req->execute();
    }

    // Récupérer tous les sujets (sans pagination)
    public function getAll()
    {
        $sql = "SELECT * FROM sujet ORDER BY id_sujet DESC";
        $liste = $this->pdo->query($sql);

        return $liste->fetchAll();
    }

    // Supprimer un sujet
    public function delete($id_sujet)
    {
        $sql = "DELETE FROM sujet WHERE id_sujet = :id_sujet";
        $req = $this->pdo->prepare($sql);
        $req->bindValue(':id_sujet', $id_sujet);
        $req->execute();
    }

    // Récupérer un sujet par id
    public function getById($id_sujet)
    {
        $sql = "SELECT * FROM sujet WHERE id_sujet = :id_sujet";
        $req = $this->pdo->prepare($sql);
        $req->bindValue(':id_sujet', $id_sujet);
        $req->execute();

        return $req->fetch();
    }

    // Mettre à jour un sujet
    public function update($sujet)
    {
        $sql = "UPDATE sujet
                SET titre = :titre, categorie = :categorie
                WHERE id_sujet = :id_sujet";

        $req = $this->pdo->prepare($sql);

        $req->bindValue(':id_sujet', $sujet->getIdSujet());
        $req->bindValue(':titre', $sujet->getTitre());
        $req->bindValue(':categorie', $sujet->getCategorie());

        $req->execute();
    }

    // ============================
    //   MÉTHODES POUR PAGINATION
    // ============================

    // Nombre total de sujets
    public function countAll()
    {
        $sql = "SELECT COUNT(*) AS total FROM sujet";
        $req = $this->pdo->query($sql);
        $row = $req->fetch();

        $total = 0;
        if (isset($row['total'])) {
            $total = (int) $row['total'];
        }

        return $total;
    }

    // Récupérer une page de sujets
    public function getPage($limit, $offset)
    {
        $sql = "SELECT * FROM sujet
                ORDER BY id_sujet DESC
                LIMIT :offset, :limit";

        $req = $this->pdo->prepare($sql);

        $req->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $req->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

        $req->execute();

        return $req->fetchAll();
    }
}
?>
