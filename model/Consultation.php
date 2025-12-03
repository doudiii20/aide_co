<?php
class RendezVous
{
    private $id_rendezVous;
    private $nom_complet;
    private $email_universitaire;
    private $telephone;
    private $type_rendezVous;
    private $date_souhaitee;
    private $heure;
    private $duree;
    private $description;

    public function __construct(
        $id_rendezVous,
        $nom_complet,
        $email_universitaire,
        $telephone,
        $type_rendezVous,
        $date_souhaitee,
        $heure,
        $duree,
        $description
    ) {
        $this->id_rendezVous      = $id_rendezVous;
        $this->nom_complet          = $nom_complet;
        $this->email_universitaire  = $email_universitaire;
        $this->telephone            = $telephone;
        $this->type_rendezVous    = $type_rendezVous;
        $this->date_souhaitee       = $date_souhaitee;
        $this->heure                = $heure;
        $this->duree                = $duree;
        $this->description          = $description;
    }

    // Getters
    public function getId() { return $this->id_rendezVous; }
    public function getNomComplet() { return $this->nom_complet; }
    public function getEmail() { return $this->email_universitaire; }
    public function getTelephone() { return $this->telephone; }
    public function getTypeRendezVous() { return $this->type_rendezVous; }
    public function getDateSouhaitee() { return $this->date_souhaitee; }
    public function getHeure() { return $this->heure; }
    public function getDuree() { return $this->duree; }
    public function getDescription() { return $this->description; }

    // Setters
    public function setNomComplet($nom) { $this->nom_complet = $nom; }
    public function setEmail($email) { $this->email_universitaire = $email; }
    public function setTelephone($tel) { $this->telephone = $tel; }
    public function setTypeRendezVous($type) { $this->type_rendezVous = $type; }
    public function setDateSouhaitee($date) { $this->date_souhaitee = $date; }
    public function setHeure($heure) { $this->heure = $heure; }
    public function setDuree($duree) { $this->duree = $duree; }
    public function setDescription($desc) { $this->description = $desc; }

    // Convert to array
    public function toArray() {
        return [
            'id_rendezVous'      => $this->id_rendezVous,
            'nom_complet'          => $this->nom_complet,
            'email_universitaire'  => $this->email_universitaire,
            'telephone'            => $this->telephone,
            'type_rendezVous'    => $this->type_rendezVous,
            'date_souhaitee'       => $this->date_souhaitee,
            'heure'                => $this->heure,
            'duree'                => $this->duree,
            'description'          => $this->description,
        ];
    }

    // Créer un objet à partir d'un tableau
    public static function fromArray(array $data) {
        return new self(
            $data['id_rendezVous'],
            $data['nom_complet'],
            $data['email_universitaire'],
            $data['telephone'],
            $data['type_rendezVous'],
            $data['date_souhaitee'],
            $data['heure'],
            $data['duree'],
            $data['description']
        );
    }
}
?>

