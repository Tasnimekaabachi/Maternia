<?php

namespace App\Controller\Api;

use App\Entity\Commande;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payment')]
final class PaymentController extends AbstractController
{
    #[Route('/create-intent/{id}', name: 'api_payment_create_intent', methods: ['POST'])]
    public function createIntent(Commande $commande, PaymentService $paymentService): JsonResponse
    {
        if ($commande->getPaymentStatus() === 'paid') {
            return $this->json(['error' => 'Commande déjà payée'], Response::HTTP_BAD_REQUEST);
        }

        if (!$paymentService->isConfigured()) {
            return $this->json(['error' => 'Paiement non configuré'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $clientSecret = $paymentService->createPaymentIntent($commande);

        if (!$clientSecret) {
            return $this->json(['error' => 'Impossible de créer l\'intention de paiement'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['clientSecret' => $clientSecret]);
    }

    #[Route('/webhook', name: 'api_payment_webhook', methods: ['POST'])]
    public function webhook(Request $request, PaymentService $paymentService): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature', '');

        if ($paymentService->handleWebhook($payload, $signature)) {
            return new Response('OK', Response::HTTP_OK);
        }

        return new Response('Invalid', Response::HTTP_BAD_REQUEST);
    }
}
