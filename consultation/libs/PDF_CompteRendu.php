<?php
require_once __DIR__ . '/FPDF/fpdf.php';

class PDF_CompteRendu extends FPDF
{
    private $compteRendu;
    private $consultation;

    public function setData($compteRendu, $consultation = null)
    {
        $this->compteRendu = $compteRendu;
        $this->consultation = $consultation;
    }

    function Header()
    {
        // Logo (avec gestion d'erreur)
        $logoPath = __DIR__ . '/../uploads/logoN.png';
        if (file_exists($logoPath)) {
            try {
                $this->Image($logoPath, 10, 6, 25);
            } catch (Exception $e) {
                // Logo non valide, on continue sans
            }
        }
        
        // Titre
        $this->SetFont('Helvetica', 'B', 18);
        $this->SetTextColor(211, 47, 47);
        $this->Cell(0, 10, 'SUPPORTINI.TN', 0, 0, 'C');
        $this->Ln(8);
        
        // Sous-titre
        $this->SetFont('Helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Compte Rendu / Ordonnance', 0, 1, 'C');
        
        // Ligne de separation
        $this->SetDrawColor(211, 47, 47);
        $this->SetLineWidth(0.5);
        $this->Line(10, 30, 200, 30);
        $this->Ln(15);
    }

    function Footer()
    {
        $this->SetY(-25);
        
        // Ligne de separation
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(5);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 5, 'Document genere automatiquement par SUPPORTINI.TN', 0, 1, 'C');
        $this->Cell(0, 5, 'Page ' . $this->PageNo() . '/{nb} - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }

    function generateCompteRendu()
    {
        $this->AliasNbPages();
        $this->AddPage();
        
        // Informations du document
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0, 0, 0);
        
        // Date du document
        $this->Cell(0, 8, 'Date: ' . date('d/m/Y'), 0, 1, 'R');
        $this->Cell(0, 8, 'Reference: CR-' . str_pad($this->compteRendu['id_compte_rendu'], 5, '0', STR_PAD_LEFT), 0, 1, 'R');
        $this->Ln(5);

        // Section Patient
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(211, 47, 47);
        $this->Cell(0, 10, 'INFORMATIONS PATIENT', 0, 1, 'L');
        
        $this->SetFont('Helvetica', '', 11);
        $this->SetTextColor(0, 0, 0);
        
        // Tableau informations
        $this->SetFillColor(250, 250, 250);
        $this->Cell(50, 8, 'Nom:', 1, 0, 'L', true);
        $this->Cell(0, 8, $this->utf8_decode_safe($this->compteRendu['nom']), 1, 1, 'L');
        
        $this->Cell(50, 8, 'Email:', 1, 0, 'L', true);
        $this->Cell(0, 8, $this->utf8_decode_safe($this->compteRendu['email']), 1, 1, 'L');
        
        // Statut avec couleur
        $this->Cell(50, 8, 'Statut:', 1, 0, 'L', true);
        $statut = $this->getStatutLabel($this->compteRendu['statut']);
        $this->Cell(0, 8, $statut, 1, 1, 'L');
        
        $this->Ln(10);

        // Section Consultation (si liee)
        if ($this->consultation) {
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor(211, 47, 47);
            $this->Cell(0, 10, 'CONSULTATION ASSOCIEE', 0, 1, 'L');
            
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(0, 0, 0);
            
            if (!empty($this->consultation['date_consultation'])) {
                $this->Cell(50, 8, 'Date consultation:', 1, 0, 'L', true);
                $this->Cell(0, 8, date('d/m/Y', strtotime($this->consultation['date_consultation'])), 1, 1, 'L');
            }
            
            if (!empty($this->consultation['heure_consultation'])) {
                $this->Cell(50, 8, 'Heure:', 1, 0, 'L', true);
                $this->Cell(0, 8, $this->consultation['heure_consultation'], 1, 1, 'L');
            }
            
            if (!empty($this->consultation['motif_consultation'])) {
                $this->Cell(50, 8, 'Motif:', 1, 0, 'L', true);
                $this->Cell(0, 8, $this->utf8_decode_safe(substr($this->consultation['motif_consultation'], 0, 60)), 1, 1, 'L');
            }
            
            $this->Ln(10);
        }

        // Section Description / Ordonnance
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(211, 47, 47);
        $this->Cell(0, 10, 'COMPTE RENDU / ORDONNANCE', 0, 1, 'L');
        
        $this->SetFont('Helvetica', '', 11);
        $this->SetTextColor(0, 0, 0);
        
        // Cadre pour la description
        $this->SetDrawColor(200, 200, 200);
        $this->SetFillColor(255, 255, 255);
        
        $description = $this->utf8_decode_safe($this->compteRendu['description']);
        $this->MultiCell(0, 7, $description, 1, 'L');
        
        $this->Ln(15);

        // Section Signature
        $this->SetFont('Helvetica', 'B', 11);
        $this->Cell(95, 8, 'Signature du praticien:', 0, 0, 'L');
        $this->Cell(95, 8, 'Cachet:', 0, 1, 'L');
        
        // Zones de signature
        $this->SetDrawColor(200, 200, 200);
        $this->Rect(10, $this->GetY(), 85, 30);
        $this->Rect(110, $this->GetY(), 85, 30);
        
        $this->Ln(35);

        // Note de bas
        $this->SetFont('Helvetica', 'I', 9);
        $this->SetTextColor(128, 128, 128);
        $this->MultiCell(0, 5, 'Ce document est strictement confidentiel et reserve a usage medical. Toute reproduction ou diffusion est interdite sans autorisation.', 0, 'C');
    }

    private function getStatutLabel($statut)
    {
        $labels = [
            'rendezVous_termine' => 'Rendez-vous termine',
            'autre_rendezVous_necessaire' => 'Autre rendez-vous necessaire',
            'suivi_recommande' => 'Suivi recommande'
        ];
        return $labels[$statut] ?? $statut;
    }

    private function utf8_decode_safe($str)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $str);
    }
}
?>
