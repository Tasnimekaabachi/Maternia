<?php

namespace App\Service\Shipping;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Suivi de colis DHL via Shipment Tracking Unified API.
 * Doc : https://developer.dhl.com/api-reference/shipment-tracking
 * Auth : DHL-API-Key (clé séparée de MyDHL, à demander sur developer.dhl.com)
 */
final class DhlTrackingClient
{
    private const TRACKING_URL = 'https://api-eu.dhl.com/track/shipments';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey = '',
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Récupère le statut d'un colis par son numéro de suivi.
     *
     * @return array{status: string, description: string, events: array, deliveryDate?: string, service?: string}|null
     */
    public function track(string $trackingNumber, ?string $service = 'express'): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $trackingNumber = trim($trackingNumber);
        if ($trackingNumber === '') {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', self::TRACKING_URL, [
                'query' => [
                    'trackingNumber' => $trackingNumber,
                    'service' => $service ?? 'express',
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'DHL-API-Key' => $this->apiKey,
                ],
            ]);
            $data = $response->toArray();
        } catch (\Throwable) {
            return null;
        }

        $shipments = $data['shipments'] ?? [];
        if ($shipments === []) {
            return null;
        }

        $shipment = $shipments[0];
        $status = $shipment['status']['statusCode'] ?? 'UNKNOWN';
        $description = $shipment['status']['description'] ?? $shipment['status']['status'] ?? '';

        $events = [];
        foreach ($shipment['events'] ?? [] as $evt) {
            $events[] = [
                'date' => $evt['date'] ?? null,
                'time' => $evt['time'] ?? null,
                'location' => $evt['location']['address']['addressLocality'] ?? null,
                'description' => $evt['description'] ?? $evt['statusCode'] ?? '',
                'statusCode' => $evt['statusCode'] ?? null,
            ];
        }

        usort($events, static function (array $a, array $b): int {
            $dateA = ($a['date'] ?? '') . ' ' . ($a['time'] ?? '');
            $dateB = ($b['date'] ?? '') . ' ' . ($b['time'] ?? '');
            return strcmp($dateB, $dateA);
        });

        $result = [
            'status' => $status,
            'description' => $description,
            'events' => $events,
        ];

        if (isset($shipment['estimatedTimeOfDelivery'])) {
            $result['deliveryDate'] = $shipment['estimatedTimeOfDelivery'];
        }
        if (isset($shipment['service']['productName'])) {
            $result['service'] = $shipment['service']['productName'];
        }

        return $result;
    }
}
