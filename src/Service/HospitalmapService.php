<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HospitalmapService{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    ) {}

    public function getHospitalsNear(float $lat, float $lon, int $radius = 5000): array
    {
        try {
            $response = $this->client->request('GET', 'https://api.geoapify.com/v2/places', [
                'query' => [
                    'categories' => 'healthcare.hospital,healthcare.clinic_or_praxis,healthcare.pharmacy',
                    'filter'     => "circle:{$lon},{$lat},{$radius}",
                    'limit'      => 10,
                    'apiKey'     => $this->apiKey,
                ]
            ]);

            $data = $response->toArray();
            $hospitals = [];

            foreach ($data['features'] ?? [] as $feature) {
                $props = $feature['properties'];
                $hospitals[] = [
                    'name'     => $props['name'] ?? 'Établissement de santé',
                    'address'  => $props['formatted'] ?? '',
                    'category' => $props['categories'][0] ?? 'healthcare',
                    'lat'      => $feature['geometry']['coordinates'][1],
                    'lon'      => $feature['geometry']['coordinates'][0],
                ];
            }

            return $hospitals;

        } catch (\Exception $e) {
            return [];
        }
    }
}