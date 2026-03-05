<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RiskPredictionService
{
    public function __construct(private HttpClientInterface $client) {}
/** @param array<mixed> $symptomes
 *  @return array<mixed> */
    public function predict(array $symptomes): array
    {
        try {
            $response = $this->client->request('POST', 'http://localhost:5001/predict', [
                'json' => $symptomes,
                'timeout' => 5
            ]);

            return $response->toArray();

        } catch (\Exception $e) {
            return [
                'risk' => 'unknown',
                'message' => 'Service de prédiction indisponible.'
            ];
        }
    }
}