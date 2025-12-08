<?php
require_once __DIR__ . '/../model/Consultation.php';

class RendezVousController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Ajouter un rendez-vous
    public function addRendezVous(RendezVous $rendezVous)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO consultations
            (nom_complet, email_universitaire, telephone, type_consultation, date_souhaitee, heure, duree, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $rendezVous->getNomComplet(),
            $rendezVous->getEmail(),
            $rendezVous->getTelephone(),
            $rendezVous->getTypeRendezVous(),
            $rendezVous->getDateSouhaitee(),
            $rendezVous->getHeure(),
            $rendezVous->getDuree(),
            $rendezVous->getDescription()
        ]);
    }

    // Récupérer tous les rendez-vous
    public function getAllRendezVous()
    {
        $stmt = $this->pdo->query("SELECT * FROM consultations ORDER BY date_souhaitee DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Mapper les colonnes de la base de données vers les noms du modèle
        return array_map(function($row) {
            return [
                'id_rendezVous' => $row['id_consultation'],
                'nom_complet' => $row['nom_complet'],
                'email_universitaire' => $row['email_universitaire'],
                'telephone' => $row['telephone'],
                'type_rendezVous' => $row['type_consultation'],
                'date_souhaitee' => $row['date_souhaitee'],
                'heure' => $row['heure'],
                'duree' => $row['duree'],
                'description' => $row['description']
            ];
        }, $results);
    }

    // Récupérer un rendez-vous par ID
    public function getRendezVousById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM consultations WHERE id_consultation = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            // Mapper les colonnes de la base de données vers les noms du modèle
            $mappedData = [
                'id_rendezVous' => $data['id_consultation'],
                'nom_complet' => $data['nom_complet'],
                'email_universitaire' => $data['email_universitaire'],
                'telephone' => $data['telephone'],
                'type_rendezVous' => $data['type_consultation'],
                'date_souhaitee' => $data['date_souhaitee'],
                'heure' => $data['heure'],
                'duree' => $data['duree'],
                'description' => $data['description']
            ];
            return RendezVous::fromArray($mappedData);
        }
        return null;
    }

    // Modifier un rendez-vous
    public function updateRendezVous(RendezVous $rendezVous)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE consultations SET
            nom_complet = ?, 
            email_universitaire = ?, 
            telephone = ?, 
            type_consultation = ?, 
            date_souhaitee = ?, 
            heure = ?, 
            duree = ?, 
            description = ?
            WHERE id_consultation = ?"
        );
        return $stmt->execute([
            $rendezVous->getNomComplet(),
            $rendezVous->getEmail(),
            $rendezVous->getTelephone(),
            $rendezVous->getTypeRendezVous(),
            $rendezVous->getDateSouhaitee(),
            $rendezVous->getHeure(),
            $rendezVous->getDuree(),
            $rendezVous->getDescription(),
            $rendezVous->getId()
        ]);
    }

    // Supprimer un rendez-vous
    public function deleteRendezVous($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM consultations WHERE id_consultation = ?");
        return $stmt->execute([$id]);
    }
}
?>

