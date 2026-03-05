<?php

namespace App\Controller\Api;

use App\Service\Shipping\ShippingQuoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API frais de livraison. Utilise DHL (MyDHL API) si carrier=DHL et configuré, sinon Aramex ou formule interne.
 */
#[Route('/api/shipping')]
final class ShippingController extends AbstractController
{
    #[Route('/quote', name: 'api_shipping_quote', methods: ['GET'])]
    public function quote(Request $request, ShippingQuoteService $shippingQuoteService): JsonResponse
    {
        $weight = $request->query->get('weight', 1);
        $weightKg = max(0.1, (float) $weight);
        $country = strtoupper($request->query->get('country', 'TN'));
        $carrier = strtoupper($request->query->get('carrier', 'DHL'));

        $destination = null;
        if ($request->query->has('postal_code') || $request->query->has('city') || $request->query->has('address')) {
            $destination = [
                'country' => $country,
                'postalCode' => $request->query->get('postal_code', ''),
                'city' => $request->query->get('city', ''),
                'address' => $request->query->get('address', ''),
            ];
        }

        $quote = $shippingQuoteService->quoteByWeight($weightKg, $country, $carrier, $destination);

        return $this->json([
            'carrier' => $quote->carrier,
            'cost' => $quote->cost,
            'currency' => 'TND',
            'etaDays' => $quote->etaDays,
            'productCode' => $quote->productCode,
        ]);
    }
}
