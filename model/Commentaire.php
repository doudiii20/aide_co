<?php
// model/Commentaire.php

class Commentaire
{
    // Attributs privés
    private $id_commentaire;
    private $contenu;
    private $date_commentaire;
    private $id_sujet;

    // Constructeur
    public function __construct($contenu, $id_sujet, $id_commentaire = null, $date_commentaire = null)
    {
        $this->id_commentaire   = $id_commentaire;
        $this->contenu          = $contenu;
        $this->id_sujet         = $id_sujet;
        $this->date_commentaire = $date_commentaire;
    }

    // GETTERS
    public function getIdCommentaire()
    {
        return $this->id_commentaire;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function getDateCommentaire()
    {
        return $this->date_commentaire;
    }

    public function getIdSujet()
    {
        return $this->id_sujet;
    }

    // SETTERS
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;
    }

    public function setDateCommentaire($date_commentaire)
    {
        $this->date_commentaire = $date_commentaire;
    }

    public function setIdSujet($id_sujet)
    {
        $this->id_sujet = $id_sujet;
    }
}
?>
