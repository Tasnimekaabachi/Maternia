<?php

namespace App\Service;

use App\Entity\ReservationClient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private string $fromEmail;
    private string $fromName;
    private ?string $twilioSid;
    private ?string $twilioToken;
    private ?string $twilioSmsFrom;
    private ?string $twilioWhatsAppFrom;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        string $fromEmail = 'noreply@maternia.tn',
        string $fromName = 'Maternia Clinique',
        ?string $twilioSid = null,
        ?string $twilioToken = null,
        ?string $twilioSmsFrom = null,
        ?string $twilioWhatsAppFrom = null,
    ) {
        $this->fromEmail = $fromEmail ?: 'noreply@maternia.tn';
        $this->fromName = $fromName ?: 'Maternia Clinique';
        $this->twilioSid = $twilioSid;
        $this->twilioToken = $twilioToken;
        $this->twilioSmsFrom = $twilioSmsFrom;
        $this->twilioWhatsAppFrom = $twilioWhatsAppFrom;
    }

    /**
     * Envoyer l'email de confirmation avec lien Google Meet
     */
    public function sendConfirmationEmail(
        ReservationClient $reservation,
        ?string $meetLink = null,
        ?string $customEmail = null
    ): array {
        $targetEmail = $customEmail ?: $reservation->getEmailClient();

        if (empty($targetEmail)) {
            return ['success' => false, 'message' => 'Aucun email de destination trouvé.'];
        }

        try {
            $htmlContent = $this->twig->render('emails/reservation_confirmation.html.twig', [
                'reservation' => $reservation,
                'meetLink' => $meetLink,
            ]);

            $subject = sprintf(
                '✅ [Maternia] Confirmation RDV #%s — %s %s',
                $reservation->getReference(),
                $reservation->getPrenomClient(),
                $reservation->getNomClient()
            );

            $email = (new Email())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to(new Address($targetEmail, $reservation->getPrenomClient() . ' ' . $reservation->getNomClient()))
                ->subject($subject)
                ->html($htmlContent);

            // Text fallback
            $textContent = $this->buildTextVersion($reservation, $meetLink);
            $email->text($textContent);

            $this->mailer->send($email);

            $this->logger->info('Email confirmation envoyé', [
                'reservation' => $reservation->getReference(),
                'to' => $targetEmail,
                'meetLink' => $meetLink,
            ]);

            return [
                'success' => true,
                'message' => 'Email envoyé avec succès à ' . $targetEmail,
                'to' => $targetEmail,
            ];

        } catch (\Throwable $e) {
            $this->logger->error('Échec envoi email', [
                'error' => $e->getMessage(),
                'reservation' => $reservation->getReference(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envoyer un SMS de confirmation (via Twilio ou simulation)
     */
    public function sendConfirmationSms(
        ReservationClient $reservation,
        ?string $meetLink = null,
        ?string $customPhone = null
    ): array {
        $phone = $customPhone ?: $reservation->getTelephoneClient();

        if (empty($phone)) {
            return ['success' => false, 'message' => 'Aucun numéro de téléphone fourni.'];
        }

        // Formater le numéro (Tunisie +216 par défaut)
        $formattedPhone = $this->formatPhoneNumber($phone);

        $message = $this->buildSmsMessage($reservation, $meetLink);

        $smsFrom = $this->twilioSmsFrom;
        // rétrocompatibilité: ancienne variable d'env TWILIO_FROM
        if (!$smsFrom) {
            $envFrom = getenv('TWILIO_FROM');
            if (is_string($envFrom) && trim($envFrom) !== '') {
                $smsFrom = $envFrom;
            }
        }

        // Si Twilio est configuré, utiliser l'API réelle
        if (!empty($this->twilioSid) && !empty($this->twilioToken) && !empty($smsFrom)) {
            return $this->sendViaTwilio($formattedPhone, $smsFrom, $message, $reservation);
        }

        // Fallback : simulation (log seulement)
        $this->logger->warning('Twilio non configuré (SID, Token ou From manquant). Mode simulation activé.');
        return $this->simulateSms($formattedPhone, $message, $reservation);
    }

    /**
     * Générer un lien WhatsApp ou envoyer via API (simulation ici)
     */
    public function sendWhatsAppMessage(
        ReservationClient $reservation,
        ?string $meetLink = null,
        ?string $customPhone = null
    ): array {
        $phone = $customPhone ?: $reservation->getTelephoneClient();

        if (empty($phone)) {
            return ['success' => false, 'message' => 'Aucun numéro de téléphone trouvé.'];
        }

        $formattedPhone = $this->formatPhoneNumber($phone);
        $message = $this->buildSmsMessage($reservation, $meetLink, true);

        // Envoi réel via Twilio WhatsApp si configuré
        if ($this->twilioSid && $this->twilioToken && $this->twilioWhatsAppFrom) {
            if (!str_starts_with($this->twilioWhatsAppFrom, 'whatsapp:')) {
                return [
                    'success' => false,
                    'message' => "WhatsApp non configuré: TWILIO_WHATSAPP_FROM doit être au format whatsapp:+XXXXXXXX.",
                ];
            }

            $to = 'whatsapp:' . $formattedPhone;
            return $this->sendViaTwilio($to, $this->twilioWhatsAppFrom, $message, $reservation);
        }

        // Fallback: lien wa.me (utile si Twilio n'est pas configuré)
        $digits = preg_replace('/\D/', '', $formattedPhone);
        if (str_starts_with($digits, '216') && strlen($digits) === 11) {
            // ok
        }
        $encodedMsg = urlencode($message);
        $waLink = "https://wa.me/{$digits}?text={$encodedMsg}";

        $this->logger->info('Lien WhatsApp généré', [
            'to' => $digits,
            'link' => $waLink
        ]);

        return [
            'success' => true,
            'message' => 'WhatsApp simulé: lien généré (configurez Twilio pour envoi réel).',
            'waLink' => $waLink,
            'to' => $digits,
            'simulated' => true,
        ];
    }

    /**
     * Envoyer via Twilio API
     */
    private function sendViaTwilio(string $to, string $from, string $message, ReservationClient $reservation): array
    {
        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->twilioSid}/Messages.json";

            $data = http_build_query([
                'To'   => $to,
                'From' => $from,
                'Body' => $message,
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD        => "{$this->twilioSid}:{$this->twilioToken}",
                CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
                CURLOPT_TIMEOUT        => 15,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \RuntimeException('cURL error: ' . $curlError);
            }

            $responseData = json_decode($response, true);

            if ($httpCode >= 200 && $httpCode < 300) {
                $this->logger->info('SMS Twilio envoyé', [
                    'to' => $to,
                    'sid' => $responseData['sid'] ?? 'N/A',
                    'reservation' => $reservation->getReference(),
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS envoyé avec succès au ' . $to,
                    'sid' => $responseData['sid'] ?? null,
                ];
            } else {
                $errorMsg = $responseData['message'] ?? 'Erreur inconnue Twilio';
                throw new \RuntimeException("Twilio HTTP {$httpCode}: {$errorMsg}");
            }

        } catch (\Throwable $e) {
            $this->logger->error('Échec SMS Twilio', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return [
                'success' => false,
                'message' => 'Erreur SMS : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Simulation SMS (mode dev / sans Twilio configuré)
     */
    private function simulateSms(string $to, string $message, ReservationClient $reservation): array
    {
        $this->logger->info('[SMS SIMULÉ] Message qui serait envoyé', [
            'to' => $to,
            'message' => $message,
            'reservation' => $reservation->getReference(),
        ]);

        return [
            'success' => true,
            'message' => "SMS simulé envoyé au {$to} (mode dev - configurez Twilio pour envoi réel)",
            'simulated' => true,
            'content' => $message,
        ];
    }

    /**
     * Construire le message SMS / WhatsApp
     */
    private function buildSmsMessage(ReservationClient $reservation, ?string $meetLink, bool $isWhatsApp = false): string
    {
        if ($isWhatsApp) {
            $prefix = "MATERNIA";
            $msg = "{$prefix} : Confirmation de votre RDV.\n";
            $msg .= "Ref: #{$reservation->getReference()}\n";
            if ($meetLink) { $msg .= "Lien Meet: {$meetLink}"; }
            return $msg;
        }

        // SMS ultra simple pour la Tunisie
        $msg = "MATERNIA: Votre RDV est confirme. Ref: #" . $reservation->getReference();
        if ($meetLink) {
            $msg .= ". Lien Meet: " . $meetLink;
        }
        
        return $msg;
    }

    /**
     * Construire la version texte de l'email
     */
    private function buildTextVersion(ReservationClient $reservation, ?string $meetLink): string
    {
        $creneau = $reservation->getConsultationCreneau();
        $date = 'N/A';
        $heure = 'N/A';
        $medecin = 'N/A';
        $specialite = 'N/A';

        if ($creneau) {
            $medecin = 'Dr. ' . $creneau->getNomMedecin();
            $specialite = $creneau->getConsultation() ? $creneau->getConsultation()->getCategorie() : 'N/A';
            if ($creneau->getJour()) {
                $date = $creneau->getJour()->format('d/m/Y');
            }
            if ($creneau->getHeureDebut() && $creneau->getHeureFin()) {
                $heure = $creneau->getHeureDebut()->format('H:i') . ' - ' . $creneau->getHeureFin()->format('H:i');
            }
        }

        $text = "=== MATERNIA - Confirmation de Réservation ===\n\n";
        $text .= "Bonjour {$reservation->getPrenomClient()} {$reservation->getNomClient()},\n\n";
        $text .= "Votre rendez-vous a bien été confirmé.\n\n";
        $text .= "--- DÉTAILS ---\n";
        $text .= "Référence    : #{$reservation->getReference()}\n";
        $text .= "Médecin      : {$medecin}\n";
        $text .= "Spécialité   : {$specialite}\n";
        $text .= "Date         : {$date}\n";
        $text .= "Horaire      : {$heure}\n";
        $text .= "Statut       : {$reservation->getStatutReservation()}\n\n";

        if ($meetLink) {
            $text .= "--- GOOGLE MEET ---\n";
            $text .= "Rejoignez votre consultation via : {$meetLink}\n\n";
        }

        $text .= "--- INFORMATIONS IMPORTANT ---\n";
        $text .= "Conservez votre référence #{$reservation->getReference()} pour toute demande de modification.\n";
        $text .= "En cas d'empêchement, annulez au moins 24h à l'avance.\n\n";
        $text .= "© Maternia Clinique - Votre santé maternelle & pédiatrique, notre priorité.\n";

        return $text;
    }

    /**
     * Formater le numéro de téléphone (Tunisie +216)
     */
    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 8) {
            return '+216' . $phone;
        }

        if (strlen($phone) === 11 && str_starts_with($phone, '216')) {
            return '+' . $phone;
        }

        if (!str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }
}
