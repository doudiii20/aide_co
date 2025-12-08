<?php
require_once __DIR__ . '/../model/CompteRendu.php';

class CompteRenduController
{
    private $pdo;
    private $hasConsultationColumn = null;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Vérifier si la colonne id_consultation existe
    private function hasConsultationColumn()
    {
        if ($this->hasConsultationColumn === null) {
            try {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM comptes_rendus LIKE 'id_consultation'");
                $this->hasConsultationColumn = $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                $this->hasConsultationColumn = false;
            }
        }
        return $this->hasConsultationColumn;
    }

    // Méthode publique pour vérifier si la colonne existe (pour les vues)
    public function consultationColumnExists()
    {
        return $this->hasConsultationColumn();
    }

    // Récupérer toutes les consultations disponibles
    public function getAllConsultations()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM consultations ORDER BY id_consultation DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la table n'existe pas encore, retourner un tableau vide
            return [];
        }
    }

    // Récupérer une consultation par ID
    public function getConsultationById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM consultations WHERE id_consultation = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // Ajouter un compte rendu
    public function addCompteRendu(CompteRendu $compteRendu)
    {
        if ($this->hasConsultationColumn()) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO comptes_rendus
                (id_consultation, nom, email, description, statut)
                VALUES (?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $compteRendu->getIdConsultation(),
                $compteRendu->getNom(),
                $compteRendu->getEmail(),
                $compteRendu->getDescription(),
                $compteRendu->getStatut()
            ]);
        } else {
            // Version sans id_consultation (pour compatibilité)
            $stmt = $this->pdo->prepare(
                "INSERT INTO comptes_rendus
                (nom, email, description, statut)
                VALUES (?, ?, ?, ?)"
            );
            return $stmt->execute([
                $compteRendu->getNom(),
                $compteRendu->getEmail(),
                $compteRendu->getDescription(),
                $compteRendu->getStatut()
            ]);
        }
    }

    // Récupérer tous les comptes rendus avec les informations de consultation(partie jointure)
    public function getAllComptesRendus()
    {
        if ($this->hasConsultationColumn()) {
            try {
                $stmt = $this->pdo->query(
                    "SELECT cr.*, 
                            c.id_consultation as consultation_id,
                            c.date_consultation,
                            c.heure_consultation,
                            c.motif_consultation
                     FROM comptes_rendus cr
                     LEFT JOIN consultations c ON cr.id_consultation = c.id_consultation
                     ORDER BY cr.id_compte_rendu DESC"
                );
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Si la jointure échoue (table consultations n'existe pas), retourner sans jointure
                $stmt = $this->pdo->query("SELECT * FROM comptes_rendus ORDER BY id_compte_rendu DESC");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            // Version sans id_consultation
            $stmt = $this->pdo->query("SELECT * FROM comptes_rendus ORDER BY id_compte_rendu DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Récupérer un compte rendu par ID
    public function getCompteRenduById($id)
    {
        if ($this->hasConsultationColumn()) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT cr.*, 
                            c.id_consultation as consultation_id,
                            c.date_consultation,
                            c.heure_consultation,
                            c.motif_consultation
                     FROM comptes_rendus cr
                     LEFT JOIN consultations c ON cr.id_consultation = c.id_consultation
                     WHERE cr.id_compte_rendu = ?"
                );
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                return $data ? CompteRendu::fromArray($data) : null;
            } catch (PDOException $e) {
                // Si la jointure échoue, essayer sans jointure
                $stmt = $this->pdo->prepare("SELECT * FROM comptes_rendus WHERE id_compte_rendu = ?");
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                return $data ? CompteRendu::fromArray($data) : null;
            }
        } else {
            // Version sans id_consultation
            $stmt = $this->pdo->prepare("SELECT * FROM comptes_rendus WHERE id_compte_rendu = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? CompteRendu::fromArray($data) : null;
        }
    }

    // Modifier un compte rendu
    public function updateCompteRendu(CompteRendu $compteRendu)
    {
        if ($this->hasConsultationColumn()) {
            $stmt = $this->pdo->prepare(
                "UPDATE comptes_rendus SET
                id_consultation = ?,
                nom = ?, 
                email = ?, 
                description = ?, 
                statut = ?
                WHERE id_compte_rendu = ?"
            );
            return $stmt->execute([
                $compteRendu->getIdConsultation(),
                $compteRendu->getNom(),
                $compteRendu->getEmail(),
                $compteRendu->getDescription(),
                $compteRendu->getStatut(),
                $compteRendu->getId()
            ]);
        } else {
            // Version sans id_consultation
            $stmt = $this->pdo->prepare(
                "UPDATE comptes_rendus SET
                nom = ?, 
                email = ?, 
                description = ?, 
                statut = ?
                WHERE id_compte_rendu = ?"
            );
            return $stmt->execute([
                $compteRendu->getNom(),
                $compteRendu->getEmail(),
                $compteRendu->getDescription(),
                $compteRendu->getStatut(),
                $compteRendu->getId()
            ]);
        }
    }

    // Supprimer un compte rendu
    public function deleteCompteRendu($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM comptes_rendus WHERE id_compte_rendu = ?");
        return $stmt->execute([$id]);
    }

    // Récupérer les comptes rendus d'une consultation spécifique
    public function getComptesRendusByConsultation($id_consultation)
    {
        if ($this->hasConsultationColumn()) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM comptes_rendus 
                     WHERE id_consultation = ? 
                     ORDER BY id_compte_rendu DESC"
                );
                $stmt->execute([$id_consultation]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                return [];
            }
        } else {
            return [];
        }
    }

    // Récupérer le nombre de comptes rendus pour une consultation
    public function countComptesRendusByConsultation($id_consultation)
    {
        if ($this->hasConsultationColumn()) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT COUNT(*) as total 
                     FROM comptes_rendus 
                     WHERE id_consultation = ?"
                );
                $stmt->execute([$id_consultation]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['total'] ?? 0;
            } catch (PDOException $e) {
                return 0;
            }
        } else {
            return 0;
        }
    }

    // ==================== STATISTIQUES DASHBOARD ====================

    public function getTotalComptesRendus()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM comptes_rendus");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function getTotalConsultations()
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM consultations");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getStatsByStatut()
    {
        $stmt = $this->pdo->query(
            "SELECT statut, COUNT(*) as total FROM comptes_rendus GROUP BY statut"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComptesRendusParMois()
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT DATE_FORMAT(c.date_consultation, '%Y-%m') as mois, COUNT(cr.id_compte_rendu) as total
                 FROM comptes_rendus cr
                 LEFT JOIN consultations c ON cr.id_consultation = c.id_consultation
                 WHERE c.date_consultation >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(c.date_consultation, '%Y-%m')
                 ORDER BY mois ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getRecentComptesRendus($limit = 5)
    {
        $limit = (int) $limit;
        if ($this->hasConsultationColumn()) {
            try {
                $stmt = $this->pdo->query(
                    "SELECT cr.*, c.date_consultation
                     FROM comptes_rendus cr
                     LEFT JOIN consultations c ON cr.id_consultation = c.id_consultation
                     ORDER BY cr.id_compte_rendu DESC LIMIT $limit"
                );
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $stmt = $this->pdo->query("SELECT * FROM comptes_rendus ORDER BY id_compte_rendu DESC LIMIT $limit");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $stmt = $this->pdo->query("SELECT * FROM comptes_rendus ORDER BY id_compte_rendu DESC LIMIT $limit");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getConsultationsSansCompteRendu()
    {
        if ($this->hasConsultationColumn()) {
            try {
                $stmt = $this->pdo->query(
                    "SELECT c.* FROM consultations c
                     LEFT JOIN comptes_rendus cr ON c.id_consultation = cr.id_consultation
                     WHERE cr.id_compte_rendu IS NULL
                     ORDER BY c.date_consultation DESC"
                );
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                return [];
            }
        }
        return [];
    }

    public function getPourcentageLiaison()
    {
        if ($this->hasConsultationColumn()) {
            try {
                $total = $this->getTotalComptesRendus();
                if ($total == 0) return 0;
                
                $stmt = $this->pdo->query(
                    "SELECT COUNT(*) as total FROM comptes_rendus WHERE id_consultation IS NOT NULL"
                );
                $lies = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                return round(($lies / $total) * 100, 1);
            } catch (PDOException $e) {
                return 0;
            }
        }
        return 0;
    }

    // ==================== STATISTIQUES RENDEZ-VOUS ====================

    public function getConsultationsParMois()
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT DATE_FORMAT(date_consultation, '%Y-%m') as mois, COUNT(*) as total
                 FROM consultations
                 WHERE date_consultation >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(date_consultation, '%Y-%m')
                 ORDER BY mois ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getConsultationsParMotif()
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT motif_consultation, COUNT(*) as total
                 FROM consultations
                 WHERE motif_consultation IS NOT NULL AND motif_consultation != ''
                 GROUP BY motif_consultation
                 ORDER BY total DESC
                 LIMIT 10"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getConsultationsAujourdhui()
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COUNT(*) as total FROM consultations WHERE DATE(date_consultation) = CURDATE()"
            );
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getConsultationsCetteSemaine()
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COUNT(*) as total FROM consultations 
                 WHERE YEARWEEK(date_consultation, 1) = YEARWEEK(CURDATE(), 1)"
            );
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getConsultationsCeMois()
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COUNT(*) as total FROM consultations 
                 WHERE MONTH(date_consultation) = MONTH(CURDATE()) 
                 AND YEAR(date_consultation) = YEAR(CURDATE())"
            );
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getProchainesConsultations($limit = 5)
    {
        $limit = (int) $limit;
        try {
            $stmt = $this->pdo->query(
                "SELECT * FROM consultations 
                 WHERE date_consultation >= CURDATE()
                 ORDER BY date_consultation ASC, heure_consultation ASC
                 LIMIT $limit"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>

