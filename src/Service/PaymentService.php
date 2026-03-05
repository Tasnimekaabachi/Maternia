<?php

namespace App\Service;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Webhook;

final class PaymentService
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
        private readonly string $currency,
        private readonly float $tndToEurRate,
        private readonly CommandeRepository $commandeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function createPaymentIntent(Commande $commande): ?string
    {
        if (!$this->secretKey || str_starts_with($this->secretKey, 'sk_test_') === false && str_starts_with($this->secretKey, 'sk_live_') === false) {
            return null;
        }

        $totalTnd = $commande->getTotal() ?? 0;
        $amountEur = $totalTnd * $this->tndToEurRate;
        $amountCents = (int) round($amountEur * 100);

        if ($amountCents < 50) {
            $amountCents = 50;
        }

        try {
            $stripe = new StripeClient($this->secretKey);
            $intent = $stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => $this->currency,
                'metadata' => [
                    'commande_id' => (string) $commande->getId(),
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            $commande->setPaymentStatus('pending_stripe');
            $this->entityManager->flush();

            return $intent->client_secret;
        } catch (ApiErrorException $e) {
            return null;
        }
    }

    public function handleWebhook(string $payload, string $signature): bool
    {
        if (!$this->webhookSecret) {
            return false;
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (\Exception $e) {
            return false;
        }

        if ($event->type !== 'payment_intent.succeeded') {
            return true;
        }

        $paymentIntent = $event->data->object;
        $commandeId = $paymentIntent->metadata->commande_id ?? null;

        if (!$commandeId) {
            return true;
        }

        $commande = $this->commandeRepository->find((int) $commandeId);
        if (!$commande) {
            return true;
        }

        $commande->setPaymentStatus('paid');
        $commande->setPaidAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->notificationService->sendOrderPaid($commande);

        return true;
    }

    /**
     * Fallback : confirmer le paiement depuis le redirect Stripe (quand le webhook n'a pas encore été reçu).
     * Retourne true si la commande a été mise à jour et l'email envoyé.
     */
    public function confirmFromRedirect(Commande $commande, string $paymentIntentId): bool
    {
        if ($commande->getPaymentStatus() === 'paid') {
            return false;
        }
        if (!$this->secretKey) {
            return false;
        }

        try {
            $stripe = new StripeClient($this->secretKey);
            $intent = $stripe->paymentIntents->retrieve($paymentIntentId);
            if ($intent->status !== 'succeeded') {
                return false;
            }
            $commandeId = $intent->metadata->commande_id ?? null;
            if (!$commandeId || (int) $commandeId !== $commande->getId()) {
                return false;
            }

            $commande->setPaymentStatus('paid');
            $commande->setPaidAt(new \DateTimeImmutable());
            $this->entityManager->flush();
            $this->notificationService->sendOrderPaid($commande);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->secretKey) && (str_starts_with($this->secretKey, 'sk_test_') || str_starts_with($this->secretKey, 'sk_live_'));
    }
}
