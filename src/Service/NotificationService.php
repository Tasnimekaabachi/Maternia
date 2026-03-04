<?php

namespace App\Service;

use App\Entity\Commande;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twilio\Rest\Client as TwilioClient;

final class NotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly string $mailerFrom,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function sendOrderPaid(Commande $commande): void
    {
        $to = $commande->getEmail();
        if ($to) {
            $to = trim($to);
        }
        if ($to) {
            $html = $this->twig->render('emails/commande_payee.html.twig', [
                'commande' => $commande,
            ]);

            $email = (new Email())
                ->from($this->mailerFrom)
                ->to($to)
                ->subject('Votre facture Maternia – Commande #' . $commande->getId())
                ->html($html);

            try {
                $this->mailer->send($email);
            } catch (\Throwable $e) {
                $this->logger?->error('Envoi email commande échoué', [
                    'commande_id' => $commande->getId(),
                    'to' => $to,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->sendSmsIfConfigured($commande);
    }

    private function sendSmsIfConfigured(Commande $commande): void
    {
        $sid = (string) getenv('TWILIO_ACCOUNT_SID');
        $token = (string) getenv('TWILIO_AUTH_TOKEN');
        $from = (string) getenv('TWILIO_FROM_NUMBER');

        $to = $commande->getTelephone();
        if ($sid === '' || $token === '' || $from === '' || !$to) {
            return;
        }

        try {
            $client = new TwilioClient($sid, $token);
            $client->messages->create($to, [
                'from' => $from,
                'body' => sprintf('Maternia: commande #%d payée. Total: %.2f TND.', $commande->getId(), (float) $commande->getTotal()),
            ]);
        } catch (\Throwable) {
            // Ne pas bloquer le checkout si SMS échoue
        }
    }
}

