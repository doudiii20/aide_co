<?php
class CompteRendu
{
    private $id_compte_rendu;
    private $id_consultation;
    private $nom;
    private $email;
    private $description;
    private $statut;

    public function __construct(
        $id_compte_rendu,
        $nom,
        $email,
        $description,
        $statut,
        $id_consultation = null
    ) {
        $this->id_compte_rendu  = $id_compte_rendu;
        $this->id_consultation  = $id_consultation;
        $this->nom              = $nom;
        $this->email            = $email;
        $this->description      = $description;
        $this->statut           = $statut;
    }

    // Getters
    public function getId() { return $this->id_compte_rendu; }
    public function getIdConsultation() { return $this->id_consultation; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getDescription() { return $this->description; }
    public function getStatut() { return $this->statut; }

    // Setters
    public function setIdConsultation($id_consultation) { $this->id_consultation = $id_consultation; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setEmail($email) { $this->email = $email; }
    public function setDescription($description) { $this->description = $description; }
    public function setStatut($statut) { $this->statut = $statut; }

    // Convert to array
    public function toArray() {
        return [
            'id_compte_rendu'   => $this->id_compte_rendu,
            'id_consultation'   => $this->id_consultation,
            'nom'               => $this->nom,
            'email'             => $this->email,
            'description'       => $this->description,
            'statut'            => $this->statut,
        ];
    }

    // Créer un objet à partir d'un tableau
    public static function fromArray(array $data) {
        return new self(
            $data['id_compte_rendu'] ?? null,
            $data['nom'],
            $data['email'],
            $data['description'],
            $data['statut'],
            $data['id_consultation'] ?? null
        );
    }
}
?>

