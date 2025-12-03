<?php
// model/Sujet.php

class Sujet
{
    // Attributs privés (encapsulation)
    private $id_sujet;
    private $titre;
    private $categorie;
    private $date_creation; // date de création en base
    private $image;         // chemin de l'image du sujet

    // Constructeur
    // $id_sujet, $date_creation et $image sont optionnels
    public function __construct($titre, $categorie, $id_sujet = null, $date_creation = null, $image = null)
    {
        $this->id_sujet      = $id_sujet;
        $this->titre         = $titre;
        $this->categorie     = $categorie;
        $this->date_creation = $date_creation;
        $this->image         = $image;
    }

    // GETTERS
    public function getIdSujet()
    {
        return $this->id_sujet;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function getDateCreation()
    {
        return $this->date_creation;
    }

    public function getImage()
    {
        return $this->image;
    }

    // SETTERS
    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
    }

    public function setDateCreation($date_creation)
    {
        $this->date_creation = $date_creation;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
}
?>
