<?php

namespace App\Service\Shipping;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Calcule la distance en km entre l'entrepôt et l'adresse de livraison
 * via OpenRouteService (geocoding + matrix). Clé gratuite : https://openrouteservice.org/dev/#/signup
 */
final class DistanceService
{
    private const ORS_GEOCODE = 'https://api.openrouteservice.org/geocode/search';
    private const ORS_MATRIX = 'https://api.openrouteservice.org/v2/matrix/driving-car';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey = '',
        private readonly string $warehouseAddress = '',
    ) {
    }

    /**
     * Retourne la distance en km entre l'adresse entrepôt et l'adresse de livraison, ou null si indisponible.
     */
    public function getDistanceInKm(string $destinationAddress): ?float
    {
        if ($this->apiKey === '' || $this->warehouseAddress === '') {
            return null;
        }

        $origin = $this->geocode($this->warehouseAddress);
        $dest = $this->geocode($destinationAddress);
        if ($origin === null || $dest === null) {
            return null;
        }

        $meters = $this->getDistanceMeters($origin, $dest);
        if ($meters === null) {
            return null;
        }

        return round($meters / 1000, 2);
    }

    /**
     * @return array{0: float, 1: float}|null [lon, lat]
     */
    private function geocode(string $address): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::ORS_GEOCODE, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                ],
                'query' => [
                    'text' => $address,
                ],
            ]);
            $data = $response->toArray();
            $features = $data['features'] ?? [];
            if ($features === []) {
                return null;
            }
            $coords = $features[0]['geometry']['coordinates'] ?? null;
            if ($coords === null || !isset($coords[0], $coords[1])) {
                return null;
            }
            return [(float) $coords[0], (float) $coords[1]];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array{0: float, 1: float} $origin [lon, lat]
     * @param array{0: float, 1: float} $dest   [lon, lat]
     */
    private function getDistanceMeters(array $origin, array $dest): ?float
    {
        try {
            $response = $this->httpClient->request('POST', self::ORS_MATRIX, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'locations' => [$origin, $dest],
                    'metrics' => ['distance'],
                ],
            ]);
            $data = $response->toArray();
            $distances = $data['distances'][0] ?? null;
            if ($distances === null || !isset($distances[1])) {
                return null;
            }
            return (float) $distances[1];
        } catch (\Throwable) {
            return null;
        }
    }
}
