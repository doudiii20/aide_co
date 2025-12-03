<?php
// controller/commentaireController.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Commentaire.php';

class CommentaireCRUD
{
    private $pdo;

    public function __construct()
    {
        // Connexion via la classe Config
        $this->pdo = Config::getConnexion();
    }

    // Ajouter un commentaire
    public function add($commentaire)
    {
        $sql = "INSERT INTO commentaire (contenu, id_sujet, date_commentaire)
                VALUES (:contenu, :id_sujet, NOW())";

        $req = $this->pdo->prepare($sql);

        $req->bindValue(':contenu', $commentaire->getContenu());
        $req->bindValue(':id_sujet', $commentaire->getIdSujet());

        $req->execute();
    }

    // Liste des commentaires d'un sujet (sans pagination)
    public function getBySujet($id_sujet)
    {
        $sql = "SELECT * FROM commentaire
                WHERE id_sujet = :id_sujet
                ORDER BY date_commentaire DESC";

        $req = $this->pdo->prepare($sql);
        $req->bindValue(':id_sujet', $id_sujet);
        $req->execute();

        return $req->fetchAll();
    }

    // Lire un commentaire par son id
    public function getById($id_commentaire)
    {
        $sql = "SELECT * FROM commentaire
                WHERE id_commentaire = :id_commentaire";

        $req = $this->pdo->prepare($sql);
        $req->bindValue(':id_commentaire', $id_commentaire);
        $req->execute();

        return $req->fetch();
    }

    // Mettre à jour un commentaire
    public function update($commentaire)
    {
        $sql = "UPDATE commentaire
                SET contenu = :contenu
                WHERE id_commentaire = :id_commentaire";

        $req = $this->pdo->prepare($sql);

        $req->bindValue(':contenu', $commentaire->getContenu());
        $req->bindValue(':id_commentaire', $commentaire->getIdCommentaire());

        $req->execute();
    }

    // Supprimer un commentaire
    public function delete($id_commentaire)
    {
        $sql = "DELETE FROM commentaire WHERE id_commentaire = :id_commentaire";

        $req = $this->pdo->prepare($sql);
        $req->bindValue(':id_commentaire', $id_commentaire);

        $req->execute();
    }

    // ============================
    //   MÉTHODES POUR PAGINATION
    // ============================

    // Nombre total de commentaires pour un sujet
    public function countBySujet($id_sujet)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM commentaire
                WHERE id_sujet = :id_sujet";

        $req = $this->pdo->prepare($sql);
        $req->bindValue(':id_sujet', $id_sujet);
        $req->execute();

        $row = $req->fetch();

        $total = 0;
        if (isset($row['total'])) {
            $total = (int) $row['total'];
        }

        return $total;
    }

    // Récupérer UNE PAGE de commentaires pour un sujet
    public function getPageBySujet($id_sujet, $limit, $offset)
    {
        $sql = "SELECT * FROM commentaire
                WHERE id_sujet = :id_sujet
                ORDER BY date_commentaire DESC
                LIMIT :offset, :limit";

        $req = $this->pdo->prepare($sql);

        $req->bindValue(':id_sujet', $id_sujet);
        $req->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $req->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

        $req->execute();

        return $req->fetchAll();
    }
}
?>
