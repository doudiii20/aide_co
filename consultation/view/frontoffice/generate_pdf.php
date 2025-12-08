<?php
error_reporting(0);
ob_start();

require_once __DIR__ . '/../../auth/config.php';
require_once __DIR__ . '/../../controller/CompteRenduController.php';
require_once __DIR__ . '/../../libs/PDF_CompteRendu.php';

$controller = new CompteRenduController($pdo);

// Vérifier si un ID est passé
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID du compte rendu manquant');
}

$id = (int) $_GET['id'];

// Récupérer le compte rendu
$compteRenduObj = $controller->getCompteRenduById($id);

if (!$compteRenduObj) {
    die('Compte rendu non trouvé');
}

// Convertir en tableau
$compteRendu = $compteRenduObj->toArray();

// Récupérer la consultation associée si elle existe
$consultation = null;
if (!empty($compteRendu['id_consultation'])) {
    $consultation = $controller->getConsultationById($compteRendu['id_consultation']);
}

// Générer le PDF
$pdf = new PDF_CompteRendu();
$pdf->setData($compteRendu, $consultation);
$pdf->generateCompteRendu();

// Nom du fichier
$filename = 'Ordonnance_' . str_pad($id, 5, '0', STR_PAD_LEFT) . '_' . date('Ymd') . '.pdf';

// Nettoyer le buffer avant d'envoyer le PDF
ob_end_clean();

// Télécharger ou afficher
if (isset($_GET['download'])) {
    $pdf->Output('D', $filename);
} else {
    $pdf->Output('I', $filename);
}
?>
