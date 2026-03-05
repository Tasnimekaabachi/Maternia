<?php

namespace App\Service\Shipping;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Création d'envois DHL Express via MyDHL API (Shipment).
 * Doc : https://developer.dhl.com/api-reference/dhl-express-mydhl-api
 * Retourne le numéro de suivi et l'étiquette PDF (base64).
 */
final class DhlShipmentClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl = '',
        private readonly string $accountNumber = '',
        private readonly string $username = '',
        private readonly string $password = '',
        private readonly string $shipperName = 'Maternia',
        private readonly string $shipperAddress = 'Tunis, Tunisia',
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->accountNumber !== ''
            && $this->username !== '' && $this->password !== '';
    }

    /**
     * Crée un envoi DHL et retourne le tracking + étiquette PDF.
     *
     * @param array{
     *   recipientName: string,
     *   address: string,
     *   city: string,
     *   postalCode: string,
     *   country: string,
     *   phone?: string,
     *   email?: string
     * } $destination
     *
     * @return array{trackingNumber: string, labelPdfBase64: string}|null
     */
    public function createShipment(
        float $weightKg,
        array $destination,
        ?string $productCode = 'N',
        ?string $reference = null
    ): ?array {
        if (!$this->isConfigured()) {
            return null;
        }

        $weightKg = max(0.1, min(999, $weightKg));
        $country = strtoupper($destination['country'] ?? 'TN');

        $plannedDate = (new \DateTimeImmutable('tomorrow'))->format('Y-m-d');
        $plannedTime = '10:00:00';

        $body = [
            'plannedShippingDateAndTime' => $plannedDate . 'T' . $plannedTime,
            'pickup' => [
                'isRequested' => false,
            ],
            'productCode' => $productCode,
            'getRateEstimates' => false,
            'accounts' => [
                ['typeCode' => 'shipper', 'number' => $this->accountNumber],
                ['typeCode' => 'payor', 'number' => $this->accountNumber],
            ],
            'valueAddedServices' => [],
            'content' => [
                'packages' => [
                    [
                        'weight' => $weightKg,
                        'dimensions' => [
                            'length' => 30,
                            'width' => 20,
                            'height' => 15,
                        ],
                        'customerReferences' => [
                            ['typeCode' => 'CU', 'value' => $reference ?? 'Maternia-' . time()],
                        ],
                    ],
                ],
                'description' => 'Goods',
                'incoterm' => 'DAP',
                'unitOfMeasurement' => 'metric',
            ],
            'outputImageProperties' => [
                'printerDPI' => 300,
                'encodingFormat' => 'pdf',
                'imageOptions' => [
                    ['typeCode' => 'label', 'templateName' => 'ECOM26_84_001'],
                ],
            ],
            'customerDetails' => [
                'shipperDetails' => [
                    'postalAddress' => [
                        'postalCode' => '1000',
                        'cityName' => 'Tunis',
                        'countryCode' => 'TN',
                        'addressLine1' => $this->shipperAddress,
                    ],
                    'contactInformation' => [
                        'email' => 'contact@maternia.tn',
                        'phone' => '+21670000000',
                        'companyName' => $this->shipperName,
                        'fullName' => $this->shipperName,
                    ],
                ],
                'receiverDetails' => [
                    'postalAddress' => [
                        'postalCode' => $destination['postalCode'] ?? '1000',
                        'cityName' => $destination['city'] ?? '',
                        'countryCode' => $country,
                        'addressLine1' => $destination['address'] ?? $destination['city'] ?? 'Address',
                    ],
                    'contactInformation' => [
                        'email' => $destination['email'] ?? 'receiver@example.com',
                        'phone' => $destination['phone'] ?? '+21600000000',
                        'fullName' => $destination['recipientName'] ?? 'Receiver',
                    ],
                ],
            ],
        ];

        if ($country !== 'TN') {
            $body['content']['exportDeclaration'] = [
                'lineItems' => [
                    [
                        'number' => 1,
                        'description' => 'Goods',
                        'price' => 1.0,
                        'quantity' => 1,
                        'commodityCodes' => [['typeCode' => 'outbound', 'value' => '8517120000']],
                        'exportReasonType' => 'permanent',
                        'manufacturerCountry' => 'TN',
                        'quantityUnitOfMeasurement' => 'PCS',
                    ],
                ],
                'invoice' => [
                    'number' => 'INV-' . ($reference ?? time()),
                    'date' => date('Y-m-d'),
                ],
            ];
        }

        try {
            $response = $this->httpClient->request('POST', rtrim($this->baseUrl, '/') . '/shipments', [
                'auth_basic' => [$this->username, $this->password],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $body,
            ]);
            $data = $response->toArray();
        } catch (\Throwable $e) {
            return null;
        }

        $shipmentTrackingNumber = $data['shipmentTrackingNumber'] ?? null;
        $documents = $data['documents'] ?? [];
        $labelPdfBase64 = null;

        foreach ($documents as $doc) {
            if (($doc['typeCode'] ?? '') === 'label') {
                $labelPdfBase64 = $doc['content'] ?? $doc['image'] ?? null;
                break;
            }
        }

        if ($shipmentTrackingNumber === null || $labelPdfBase64 === null) {
            return null;
        }

        return [
            'trackingNumber' => $shipmentTrackingNumber,
            'labelPdfBase64' => $labelPdfBase64,
        ];
    }
}
