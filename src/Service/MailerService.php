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
        private readonly Environment     $twig,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Envoie un email de bienvenue à une nouvelle maman avec PHPMailer (SMTP Gmail).
     */
    public function sendWelcomeEmail(Maman $maman): bool
    {
        if (!$maman->getEmail()) {
            return false;
        }

        // ✅ getenv() retourne string|false → PHPStan niveau 9 accepte is_string()
        $fromEmail    = getenv('MAILER_EMAIL');
        $fromPassword = getenv('MAILER_PASSWORD');

        // ✅ Fallback $_SERVER avec is_string()
        if (!is_string($fromEmail) || $fromEmail === '') {
            $rawEmail  = $_SERVER['MAILER_EMAIL'] ?? null;
            $fromEmail = is_string($rawEmail) ? $rawEmail : '';
        }
        if (!is_string($fromPassword) || $fromPassword === '') {
            $rawPassword  = $_SERVER['MAILER_PASSWORD'] ?? null;
            $fromPassword = is_string($rawPassword) ? $rawPassword : '';
        }

        if ($fromEmail === '' || $fromPassword === '') {
            $this->logger->error('MAILER_EMAIL ou MAILER_PASSWORD manquant dans .env, impossible d\'envoyer l\'email.');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // ── Config SMTP ───────────────────────────────────────
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $fromEmail;    // ✅ string garanti
            $mail->Password   = $fromPassword; // ✅ string garanti
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // ── Expéditeur / Destinataire ─────────────────────────
            $mail->setFrom($fromEmail, 'Maternia'); // ✅ string garanti
            $mail->addAddress((string) $maman->getEmail());

            // ── Contenu HTML généré par Twig ──────────────────────
            $mail->isHTML(true);
            $mail->Subject = 'Bienvenue chez Maternia 💕';
            $mail->Body    = $this->twig->render(
                'emails/maman_welcome.html.twig',
                ['maman' => $maman]
            );

            $mail->send();
            $this->logger->info('Email envoyé avec succès à ' . $maman->getEmail());
            return true;

        } catch (Exception $e) {
            $this->logger->error('Erreur PHPMailer : ' . $mail->ErrorInfo);
            return false;
        }
    }
}