<?php

namespace App\Service;

use App\Entity\Maman;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class MailerService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Envoie un email de bienvenue à une nouvelle maman avec PHPMailer (SMTP Gmail ou autre).
     */
    public function sendWelcomeEmail(Maman $maman): bool
    {
        // Pas d'email → on ne tente pas d'envoi
        if (!$maman->getEmail()) {
            return false;
        }

        // Lecture des variables d'environnement directement
        $fromEmail = $_ENV['MAILER_EMAIL'] ?? ($_SERVER['MAILER_EMAIL'] ?? null);
        $fromPassword = $_ENV['MAILER_PASSWORD'] ?? ($_SERVER['MAILER_PASSWORD'] ?? null);

        if (empty($fromEmail) || empty($fromPassword)) {
            $this->logger->error('MAILER_EMAIL ou MAILER_PASSWORD manquant dans .env, impossible d\'envoyer l\'email.');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Config SMTP (exemple Gmail)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $fromEmail;
            $mail->Password   = $fromPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Expéditeur / destinataire
            $mail->setFrom($fromEmail, 'Maternia');
            $mail->addAddress($maman->getEmail());

            // Contenu HTML généré par Twig
            $mail->isHTML(true);
            $mail->Subject = 'Bienvenue chez Maternia';
            $mail->Body = $this->twig->render(
                'emails/maman_welcome.html.twig',
                ['maman' => $maman]
            );

            // Envoi
            $mail->send();

            $this->logger->info('Email envoyé avec succès à ' . $maman->getEmail());

            return true;
        } catch (Exception $e) {
            $this->logger->error('Erreur PHPMailer: ' . $mail->ErrorInfo);

            return false;
        }
    }
}



