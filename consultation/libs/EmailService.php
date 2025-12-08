<?php
/**
 * Service d'envoi d'emails pour Supportini
 */
class EmailService
{
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $useSMTP;

    public function __construct()
    {
        // Configuration par défaut (peut être modifiée dans config.php)
        $this->fromEmail = 'noreply@supportini.tn';
        $this->fromName = 'SUPPORTINI.TN';
        $this->useSMTP = false; // Utiliser la fonction mail() de PHP par défaut
        
        // Pour utiliser SMTP, décommentez et configurez :
        // $this->useSMTP = true;
        // $this->smtpHost = 'smtp.gmail.com';
        // $this->smtpPort = 587;
        // $this->smtpUser = 'votre-email@gmail.com';
        // $this->smtpPass = 'votre-mot-de-passe';
    }

    /**
     * Envoyer un email de confirmation de compte rendu
     */
    public function sendCompteRenduConfirmation($patientEmail, $patientName, $compteRendu)
    {
        $subject = "Votre compte rendu médical - SUPPORTINI.TN";
        
        $message = $this->getCompteRenduEmailTemplate($patientName, $compteRendu);
        
        // Toujours sauvegarder pour test/debug
        $this->saveEmailForTesting($patientEmail, $patientName, $subject, $message);
        
        // En localhost, retourner true directement
        if ($this->isLocalhost()) {
            return true;
        }
        
        return $this->sendEmail($patientEmail, $patientName, $subject, $message);
    }

    /**
     * Envoyer un email de confirmation de consultation
     */
    public function sendConsultationConfirmation($patientEmail, $patientName, $consultation)
    {
        $subject = "Confirmation de votre rendez-vous - SUPPORTINI.TN";
        
        $message = $this->getConsultationEmailTemplate($patientName, $consultation);
        
        // Toujours sauvegarder pour test/debug
        $this->saveEmailForTesting($patientEmail, $patientName, $subject, $message);
        
        // En localhost, retourner true directement
        if ($this->isLocalhost()) {
            return true;
        }
        
        return $this->sendEmail($patientEmail, $patientName, $subject, $message);
    }

    /**
     * Template email pour compte rendu
     */
    private function getCompteRenduEmailTemplate($patientName, $compteRendu)
    {
        $statutLabels = [
            'rendezVous_termine' => 'Rendez-vous terminé',
            'autre_rendezVous_necessaire' => 'Autre rendez-vous nécessaire',
            'suivi_recommande' => 'Suivi recommandé'
        ];
        
        $statut = $statutLabels[$compteRendu['statut']] ?? $compteRendu['statut'];
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #b71c1c, #d32f2f); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #d32f2f; border-radius: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .btn { display: inline-block; padding: 12px 30px; background: #d32f2f; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                h2 { color: #d32f2f; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>SUPPORTINI.TN</h1>
                    <p>Compte Rendu Médical</p>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>{$patientName}</strong>,</p>
                    
                    <p>Votre compte rendu médical a été confirmé et est maintenant disponible.</p>
                    
                    <div class='info-box'>
                        <h2>Détails du compte rendu</h2>
                        <p><strong>Statut :</strong> {$statut}</p>
                        <p><strong>ID du compte rendu :</strong> #{$compteRendu['id_compte_rendu']}</p>
                        " . (!empty($compteRendu['date_consultation']) ? "<p><strong>Date de consultation :</strong> " . date('d/m/Y', strtotime($compteRendu['date_consultation'])) . "</p>" : "") . "
                    </div>
                    
                    <div class='info-box'>
                        <h2>Description / Ordonnance</h2>
                        <p>" . nl2br(htmlspecialchars($compteRendu['description'])) . "</p>
                    </div>
                    
                    <p style='text-align: center;'>
                        <a href='" . (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . "/consultation/view/frontoffice/generate_pdf.php?id={$compteRendu['id_compte_rendu']}' class='btn'>
                            Télécharger le PDF
                        </a>
                    </p>
                    
                    <p>Vous pouvez également consulter votre compte rendu à tout moment sur notre plateforme.</p>
                    
                    <p>Cordialement,<br>
                    <strong>L'équipe SUPPORTINI.TN</strong></p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                    <p>&copy; 2024 SUPPORTINI.TN - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }

    /**
     * Template email pour consultation
     */
    private function getConsultationEmailTemplate($patientName, $consultation)
    {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #b71c1c, #d32f2f); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #d32f2f; border-radius: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                h2 { color: #d32f2f; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>SUPPORTINI.TN</h1>
                    <p>Confirmation de Rendez-vous</p>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>{$patientName}</strong>,</p>
                    
                    <p>Votre rendez-vous a été confirmé avec succès.</p>
                    
                    <div class='info-box'>
                        <h2>Détails du rendez-vous</h2>
                        <p><strong>Date :</strong> " . date('d/m/Y', strtotime($consultation['date_souhaitee'])) . "</p>
                        <p><strong>Heure :</strong> {$consultation['heure']}</p>
                        <p><strong>Durée :</strong> {$consultation['duree']}</p>
                        <p><strong>Type :</strong> {$consultation['type_rendezVous']}</p>
                    </div>
                    
                    <p>Merci de confirmer votre présence ou de nous contacter en cas de changement.</p>
                    
                    <p>Cordialement,<br>
                    <strong>L'équipe SUPPORTINI.TN</strong></p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                    <p>&copy; 2024 SUPPORTINI.TN - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }

    /**
     * Envoyer un email
     */
    private function sendEmail($to, $toName, $subject, $htmlMessage, $textMessage = null)
    {
        if ($this->useSMTP) {
            return $this->sendEmailSMTP($to, $toName, $subject, $htmlMessage, $textMessage);
        } else {
            return $this->sendEmailPHP($to, $toName, $subject, $htmlMessage);
        }
    }

    /**
     * Envoyer email avec la fonction mail() de PHP
     */
    private function sendEmailPHP($to, $toName, $subject, $htmlMessage)
    {
        // Valider l'email
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("EmailService: Adresse email invalide: $to");
            return false;
        }

        // Détecter si on est en localhost/XAMPP
        $isLocalhost = $this->isLocalhost();
        
        if ($isLocalhost) {
            // En localhost, sauvegarder l'email dans un fichier pour test
            $this->saveEmailForTesting($to, $toName, $subject, $htmlMessage);
            // On retourne true pour simuler l'envoi réussi
            return true;
        }

        // Vérifier si la fonction mail est disponible
        if (!function_exists('mail')) {
            error_log("EmailService: La fonction mail() n'est pas disponible sur ce serveur");
            // Même en production, sauvegarder pour sécurité
            $this->saveEmailForTesting($to, $toName, $subject, $htmlMessage);
            return false;
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Essayer d'envoyer l'email
        $oldErrorReporting = error_reporting(0);
        $result = @mail($to, $subject, $htmlMessage, $headers);
        error_reporting($oldErrorReporting);

        if (!$result) {
            // Si l'envoi échoue, sauvegarder quand même pour debug
            $this->saveEmailForTesting($to, $toName, $subject, $htmlMessage);
            $lastError = error_get_last();
            if ($lastError && strpos($lastError['message'], 'mail') !== false) {
                error_log("EmailService: Erreur lors de l'envoi de l'email: " . $lastError['message']);
            }
        }

        return $result;
    }

    /**
     * Vérifier si on est en localhost
     */
    private function isLocalhost()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return true; // Par sécurité, on considère comme localhost
        }
        
        $host = strtolower($_SERVER['HTTP_HOST']);
        return in_array($host, ['localhost', '127.0.0.1', '::1']) || 
               strpos($host, 'localhost') !== false ||
               strpos($host, '127.0.0.1') !== false ||
               strpos($host, 'xampp') !== false ||
               strpos($host, 'local') !== false;
    }

    /**
     * Sauvegarder l'email dans un fichier pour test en localhost
     */
    private function saveEmailForTesting($to, $toName, $subject, $htmlMessage)
    {
        $testDir = __DIR__ . '/../test_emails';
        if (!is_dir($testDir)) {
            @mkdir($testDir, 0755, true);
        }

        $filename = $testDir . '/email_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
        $content = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Email de test - {$subject}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
                .email-container { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
                .email-header { background: #d32f2f; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .email-info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin-bottom: 20px; }
                .email-body { margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h2>📧 Email de Test - Mode Localhost</h2>
                </div>
                <div class='email-info'>
                    <p><strong>À :</strong> {$toName} &lt;{$to}&gt;</p>
                    <p><strong>Sujet :</strong> {$subject}</p>
                    <p><strong>Date :</strong> " . date('d/m/Y H:i:s') . "</p>
                    <p><strong>Mode :</strong> Localhost - Email sauvegardé (pas réellement envoyé)</p>
                </div>
                <div class='email-body'>
                    {$htmlMessage}
                </div>
            </div>
        </body>
        </html>
        ";

        @file_put_contents($filename, $content);
        error_log("EmailService: Email sauvegardé pour test dans: $filename");
    }

    /**
     * Envoyer email avec SMTP (PHPMailer ou autre)
     * À implémenter si nécessaire
     */
    private function sendEmailSMTP($to, $toName, $subject, $htmlMessage, $textMessage = null)
    {
        // Pour l'instant, on utilise mail() par défaut
        // Vous pouvez intégrer PHPMailer ici si nécessaire
        return $this->sendEmailPHP($to, $toName, $subject, $htmlMessage);
    }
}
?>

