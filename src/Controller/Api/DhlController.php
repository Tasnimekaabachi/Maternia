<?php

namespace App\Controller\Api;

use App\Repository\CommandeRepository;
use App\Service\Shipping\DhlShipmentClient;
use App\Service\Shipping\DhlTrackingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API DHL : création d'envois et suivi des colis.
 */
#[Route('/api/dhl')]
final class DhlController extends AbstractController
{
    #[Route('/shipment', name: 'api_dhl_shipment_create', methods: ['POST'])]
    public function createShipment(Request $request, DhlShipmentClient $shipmentClient): JsonResponse
    {
        if (!$shipmentClient->isConfigured()) {
            return $this->json(['error' => 'DHL Shipment API non configurée'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $weight = (float) ($data['weight'] ?? 1);
        $weightKg = max(0.1, $weight);
        $productCode = $data['productCode'] ?? 'N';
        $reference = $data['reference'] ?? null;

        $destination = [
            'recipientName' => $data['recipientName'] ?? $data['recipient_name'] ?? '',
            'address' => $data['address'] ?? $data['street'] ?? '',
            'city' => $data['city'] ?? '',
            'postalCode' => $data['postalCode'] ?? $data['postal_code'] ?? '1000',
            'country' => strtoupper($data['country'] ?? 'TN'),
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ];

        if ($destination['recipientName'] === '' || $destination['city'] === '') {
            return $this->json([
                'error' => 'Champs requis : recipientName, address, city',
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $shipmentClient->createShipment($weightKg, $destination, $productCode, $reference);

        if ($result === null) {
            return $this->json([
                'error' => 'Échec de la création de l\'envoi DHL',
            ], Response::HTTP_BAD_GATEWAY);
        }

        return $this->json([
            'trackingNumber' => $result['trackingNumber'],
            'labelPdfBase64' => $result['labelPdfBase64'],
            'message' => 'Envoi créé. Imprimez l\'étiquette et collez-la sur le colis.',
        ]);
    }

    #[Route('/track', name: 'api_dhl_track', methods: ['GET'])]
    public function track(
        Request $request,
        DhlTrackingClient $trackingClient,
        CommandeRepository $commandeRepository
    ): JsonResponse {
        $trackingNumber = $request->query->get('trackingNumber') ?? $request->query->get('tracking_number') ?? '';
        $service = $request->query->get('service', 'express');

        if ($trackingNumber === '') {
            return $this->json(['error' => 'Paramètre requis : trackingNumber'], Response::HTTP_BAD_REQUEST);
        }

        // 1) Suivi interne Maternia (préfixe MTR-)
        if (str_starts_with($trackingNumber, 'MTR-')) {
            $commande = $commandeRepository->findOneBy(['shippingTracking' => $trackingNumber]);
            if ($commande === null) {
                return $this->json([
                    'error' => 'Colis non trouvé (référence interne).',
                ], Response::HTTP_NOT_FOUND);
            }

            $dateCommande = $commande->getDateCommande() ?? new \DateTimeImmutable();
            $etaDays = $commande->getShippingEtaDays() ?? 2;
            $estimatedDelivery = (clone $dateCommande)->modify('+' . $etaDays . ' days');

            $events = [
                [
                    'date' => $dateCommande->format('Y-m-d'),
                    'time' => $dateCommande->format('H:i'),
                    'location' => $commande->getShippingCity() ?: null,
                    'description' => 'Commande créée sur Maternia',
                    'statusCode' => 'CREATED',
                ],
            ];

            if ($commande->getPaymentStatus() === 'paid') {
                $paidAt = $commande->getPaidAt() ?? $dateCommande;
                $events[] = [
                    'date' => $paidAt->format('Y-m-d'),
                    'time' => $paidAt->format('H:i'),
                    'location' => $commande->getShippingCity() ?: null,
                    'description' => 'Paiement confirmé',
                    'statusCode' => 'PAID',
                ];
            }

            return $this->json([
                'status' => $commande->getStatut() ?? 'En attente',
                'description' => 'Suivi interne Maternia (transporteur : ' . ($commande->getShippingCarrier() ?? 'POSTE') . ').',
                'events' => array_reverse($events),
                'deliveryDate' => $estimatedDelivery->format(\DateTimeInterface::ATOM),
                'service' => $commande->getShippingCarrier() ?? 'POSTE',
            ]);
        }

        // 2) Suivi via API DHL pour les vrais numéros DHL
        if (!$trackingClient->isConfigured()) {
            return $this->json([
                'error' => 'DHL Tracking API non configurée. Ajoutez DHL_TRACKING_API_KEY dans .env.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $result = $trackingClient->track($trackingNumber, $service);

        if ($result === null) {
            return $this->json([
                'error' => 'Colis non trouvé ou numéro invalide',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($result);
    }
}
