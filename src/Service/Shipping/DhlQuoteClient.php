<?php

namespace App\Service\Shipping;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Devis DHL Express via MyDHL API (Rating).
 * Doc : https://developer.dhl.com/api-reference/dhl-express-mydhl-api
 * Auth : Basic (username + password). Compte DHL Express requis.
 */
final class DhlQuoteClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl = '',
        private readonly string $accountNumber = '',
        private readonly string $username = '',
        private readonly string $password = '',
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->accountNumber !== ''
            && $this->username !== '' && $this->password !== '';
    }

    /**
     * Appel API Rating : tarif + délai estimé.
     * Retourne null si non configuré, erreur API ou réponse invalide.
     *
     * @param array{country: string, postalCode: string, city: string, address?: string} $destination
     */
    public function getQuote(float $weightKg, array $destination): ?ShippingQuote
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $weightKg = max(0.1, min(999, $weightKg));
        $country = strtoupper($destination['country'] ?? 'TN');
        $postalCode = $destination['postalCode'] ?? '1000';
        $city = $destination['city'] ?? '';
        $addressLine = $destination['address'] ?? $city;

        $body = [
            'accountNumber' => $this->accountNumber,
            'originAddress' => [
                'countryCode' => 'TN',
                'postalCode' => '1000',
                'cityName' => 'Tunis',
                'addressLine1' => 'Entrepot Maternia',
            ],
            'destinationAddress' => [
                'countryCode' => $country,
                'postalCode' => $postalCode,
                'cityName' => $city,
                'addressLine1' => $addressLine ?: $city,
            ],
            'packages' => [
                [
                    'weight' => $weightKg,
                    'length' => 30,
                    'width' => 20,
                    'height' => 15,
                    'unitOfMeasurement' => 'metric',
                ],
            ],
        ];

        try {
            $response = $this->httpClient->request('POST', rtrim($this->baseUrl, '/') . '/rates', [
                'auth_basic' => [$this->username, $this->password],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);
            $data = $response->toArray();
        } catch (\Throwable) {
            return null;
        }

        $products = $data['products'] ?? [];
        if ($products === []) {
            return null;
        }

        $first = $products[0];
        $totalNet = $first['totalNet'] ?? $first['totalPrice'] ?? null;
        $currency = $first['totalNetCurrency'] ?? $first['currencyCode'] ?? 'TND';
        $deliveryDate = $first['deliveryDate'] ?? null;
        $productCode = $first['productCode'] ?? 'N';

        if ($totalNet === null) {
            return null;
        }

        $cost = (float) $totalNet;
        if (strtoupper($currency) !== 'TND') {
            // Conserver la valeur si pas en TND (à afficher en devise d'origine ou convertir selon besoin)
        }

        $etaDays = 2;
        if ($deliveryDate !== null) {
            try {
                $eta = (new \DateTimeImmutable($deliveryDate))->diff(new \DateTimeImmutable('now'))->days;
                $etaDays = max(1, (int) $eta);
            } catch (\Throwable) {
                // keep default
            }
        }

        return new ShippingQuote(
            cost: round($cost, 2),
            etaDays: $etaDays,
            carrier: 'DHL',
            trackingNumber: null,
            productCode: $productCode,
        );
    }
}
