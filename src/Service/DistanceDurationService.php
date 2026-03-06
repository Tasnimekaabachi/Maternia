<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Calcule la distance et le temps de trajet entre deux lieux (Tunisie)
 * via OpenStreetMap (Nominatim pour le géocodage, OSRM pour l'itinéraire).
 */
final class DistanceDurationService
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';
    private const OSRM_URL = 'https://router.project-osrm.org/route/v1/driving';
    private const USER_AGENT = 'MaterniaApp/1.0 (Symfony; contact@maternia.local)';
    /** Pays par défaut pour le géocodage (villes en Tunisie) */
    private const DEFAULT_COUNTRY = 'Tunisia';
    private const COUNTRY_CODES = 'tn';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * @return array{lat: float, lon: float}|null
     */
    public function geocode(string $address): ?array
    {
        $query = trim($address);
        if ($query === '') {
            return null;
        }

        // Pour une seule ville (sans virgule), utiliser les coordonnées fixes en priorité (Tunis et tous les gouvernorats)
        // pour éviter les erreurs de géocodage Nominatim.
        if (!str_contains($query, ',')) {
            $coords = VillesTunisie::getCoordonnees($query);
            if ($coords !== null) {
                return $coords;
            }
            $normalized = VillesTunisie::normalizePourGeocodage($query);
            $query = $normalized ?? $query;
            $query .= ', ' . self::DEFAULT_COUNTRY;
        }

        $response = $this->httpClient->request('GET', self::NOMINATIM_URL, [
            'query' => [
                'q' => $query,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => self::COUNTRY_CODES,
            ],
            'headers' => [
                'User-Agent' => self::USER_AGENT,
            ],
        ]);

        $data = $response->toArray();
        if ($data === []) {
            return null;
        }

        $first = $data[0];
        $lat = (float) ($first['lat'] ?? 0);
        $lon = (float) ($first['lon'] ?? 0);

        return ['lat' => $lat, 'lon' => $lon];
    }

    /**
     * Calcule la distance et la durée en voiture entre deux points (coordonnées).
     *
     * @param array{lat: float, lon: float} $origin
     * @param array{lat: float, lon: float} $destination
     * @return array{distance_km: float, duration_minutes: float, distance_text: string, duration_text: string}|null
     */
    public function getRoute(array $origin, array $destination): ?array
    {
        $coords = sprintf('%s,%s;%s,%s', $origin['lon'], $origin['lat'], $destination['lon'], $destination['lat']);
        $url = self::OSRM_URL . '/' . $coords . '?overview=false';

        $response = $this->httpClient->request('GET', $url);
        $data = $response->toArray();

        if (($data['code'] ?? '') !== 'Ok' || empty($data['routes'])) {
            return null;
        }

        $route = $data['routes'][0];
        $distanceMeters = (float) ($route['distance'] ?? 0);
        $durationSeconds = (float) ($route['duration'] ?? 0);

        $distanceKm = round($distanceMeters / 1000, 2);
        $durationMinutes = round($durationSeconds / 60, 1);

        $distanceText = $distanceKm >= 1
            ? sprintf('%s km', number_format($distanceKm, 1, ',', ' '))
            : sprintf('%d m', (int) $distanceMeters);
        $durationText = $durationMinutes >= 60
            ? sprintf('%d h %d min', (int) ($durationMinutes / 60), (int) ($durationMinutes % 60))
            : sprintf('%d min', (int) round($durationMinutes));

        return [
            'distance_km' => $distanceKm,
            'duration_minutes' => $durationMinutes,
            'distance_text' => $distanceText,
            'duration_text' => $durationText,
        ];
    }

    /**
     * À partir d'une adresse ou d'une ville (origine) et d'une destination (ville ou adresse),
     * retourne la distance et le temps de trajet.
     *
     * @return array{distance_km: float, duration_minutes: float, distance_text: string, duration_text: string, origin_coords: array, destination_coords: array}|array{error: string}|null
     */
    public function getDistanceAndDuration(string $origin, string $destination): ?array
    {
        $originCoords = $this->parseCoordinates($origin) ?? $this->geocode($origin);
        $destCoords = $this->parseCoordinates($destination) ?? $this->geocode($destination);

        if ($originCoords === null) {
            return ['error' => 'Impossible de localiser l\'origine (adresse ou ville).'];
        }
        if ($destCoords === null) {
            return ['error' => 'Impossible de localiser la destination (ville ou adresse).'];
        }

        $route = $this->getRoute($originCoords, $destCoords);
        if ($route === null) {
            return ['error' => 'Impossible de calculer l\'itinéraire entre ces deux points.'];
        }

        $route['origin_coords'] = $originCoords;
        $route['destination_coords'] = $destCoords;
        return $route;
    }

    /**
     * Si la chaîne est au format "lat,lon", retourne ['lat' => ..., 'lon' => ...], sinon null.
     */
    private function parseCoordinates(string $input): ?array
    {
        $input = trim($input);
        if (!preg_match('/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $input)) {
            return null;
        }
        [$lat, $lon] = array_map('floatval', array_map('trim', explode(',', $input, 2)));
        return ['lat' => $lat, 'lon' => $lon];
    }
}
