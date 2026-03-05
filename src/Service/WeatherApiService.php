<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Intégration API externe Open-Meteo (gratuite, sans clé)
 * pour afficher la météo du jour – critère "Intégration des APIs" PIDEV.
 */
final class WeatherApiService
{
    private const OPEN_METEO_URL = 'https://api.open-meteo.com/v1/forecast';

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    /**
     * Récupère la météo actuelle pour une ville (par défaut Tunis).
     *
     * @return array{ temperature: float|null, weather_code: int|null, label: string }|null
     */
    public function getCurrentWeather(float $latitude = 36.8065, float $longitude = 10.1815): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::OPEN_METEO_URL, [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,weather_code',
                ],
                'timeout' => 5,
            ]);
            $data = $response->toArray();
            $current = $data['current'] ?? null;
            if (!$current) {
                return null;
            }
            $code = (int) ($current['weather_code'] ?? 0);
            return [
                'temperature' => isset($current['temperature_2m']) ? (float) $current['temperature_2m'] : null,
                'weather_code' => $code,
                'label' => $this->weatherCodeToLabel($code),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function weatherCodeToLabel(int $code): string
    {
        return match (true) {
            $code === 0 => 'Ciel dégagé',
            $code >= 1 && $code <= 3 => 'Partiellement nuageux',
            $code >= 45 && $code <= 48 => 'Brouillard',
            $code >= 51 && $code <= 67 => 'Pluie',
            $code >= 71 && $code <= 77 => 'Neige',
            $code >= 80 && $code <= 82 => 'Averses',
            $code >= 95 && $code <= 99 => 'Orage',
            default => 'Variable',
        };
    }
}
