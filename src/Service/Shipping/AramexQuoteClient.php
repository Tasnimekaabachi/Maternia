<?php

namespace App\Service\Shipping;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Devis Aramex (tarif + délai).
 * Aramex propose un Rate Calculator (souvent en XML). Ce client peut appeler un endpoint
 * REST configuré ou être étendu pour le format XML officiel.
 * Doc : https://dr.aramex.com/ae/en/developers-solution-center/aramex-apis
 */
final class AramexQuoteClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly bool $enabled = false,
        private readonly string $accountNumber = '',
        private readonly string $username = '',
        private readonly string $password = '',
        private readonly string $rateUrl = '',
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->enabled && $this->accountNumber !== ''
            && $this->username !== '' && $this->password !== ''
            && $this->rateUrl !== '';
    }

    /**
     * Retourne un devis si l'API est configurée et répond au format attendu.
     *
     * @param array{country: string, postalCode: string, city: string, address?: string} $destination
     */
    public function getQuote(float $weightKg, array $destination): ?ShippingQuote
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $weightKg = max(0.1, min(999, $weightKg));

        try {
            $response = $this->httpClient->request('POST', $this->rateUrl, [
                'auth_basic' => [$this->username, $this->password],
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'accountNumber' => $this->accountNumber,
                    'weightKg' => $weightKg,
                    'destination' => $destination,
                ],
            ]);
            $data = $response->toArray();
        } catch (\Throwable) {
            return null;
        }

        $cost = isset($data['amount']) ? (float) $data['amount'] : (isset($data['totalCharge']) ? (float) $data['totalCharge'] : null);
        $etaDays = isset($data['days']) ? (int) $data['days'] : (isset($data['deliveryDays']) ? (int) $data['deliveryDays'] : 2);
        if ($cost === null) {
            return null;
        }

        return new ShippingQuote(
            cost: round($cost, 2),
            etaDays: max(1, $etaDays),
            carrier: 'ARAMEX',
            trackingNumber: null,
            productCode: $data['productCode'] ?? null,
        );
    }
}
