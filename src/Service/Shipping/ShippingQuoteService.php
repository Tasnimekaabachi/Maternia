<?php

namespace App\Service\Shipping;

use App\Entity\Produit;

/**
 * Agrège devis livraison : APIs DHL / Aramex si configurées, sinon formule interne.
 * Peut utiliser la distance (OpenRouteService) pour affiner les frais (ex. 4 TND / 100 km).
 */
final class ShippingQuoteService
{
    public function __construct(
        private readonly DhlQuoteClient $dhlQuoteClient,
        private readonly AramexQuoteClient $aramexQuoteClient,
        private readonly DistanceService $distanceService,
    ) {
    }

    /**
     * @param Produit[] $produits
     * @param array{country?: string, postalCode?: string, city?: string, address?: string}|null $destinationAddress Pour API DHL/Aramex et calcul distance
     */
    public function quote(
        array $produits,
        ?string $country = 'TN',
        ?string $carrier = 'POSTE',
        ?array $destinationAddress = null
    ): ShippingQuote {
        $carrier = $carrier ?: 'POSTE';
        $country = $country ?: 'TN';

        $weightKg = 0.0;
        foreach ($produits as $p) {
            $weightKg += $p->getPoidsKg() ?? 0.30;
        }
        $weightKg = max(0.1, $weightKg);

        $destination = $destinationAddress ?? [
            'country' => $country,
            'postalCode' => '',
            'city' => '',
            'address' => '',
        ];
        $destination['country'] = $destination['country'] ?? $country;

        // 1) DHL : devis temps réel si configuré et transporteur DHL
        if (strtoupper($carrier) === 'DHL' && $this->dhlQuoteClient->isConfigured()) {
            $quote = $this->dhlQuoteClient->getQuote($weightKg, $destination);
            if ($quote !== null) {
                return $quote;
            }
        }

        // 2) Aramex : devis temps réel si configuré et transporteur ARAMEX
        if (strtoupper($carrier) === 'ARAMEX' && $this->aramexQuoteClient->isConfigured()) {
            $quote = $this->aramexQuoteClient->getQuote($weightKg, $destination);
            if ($quote !== null) {
                return $quote;
            }
        }

        // 3) Formule interne (base + poids + zone + optionnel distance)
        $isInternational = strtoupper($country) !== 'TN';
        $base = 5.0;
        $perKg = 2.0;
        $carrierMultiplier = match (strtoupper($carrier)) {
            'DHL' => 1.8,
            'ARAMEX' => 1.5,
            default => 1.0,
        };
        $zoneAddon = $isInternational ? 12.0 : 0.0;

        $distanceKm = null;
        $destAddress = trim(implode(' ', [
            $destination['address'] ?? '',
            $destination['postalCode'] ?? '',
            $destination['city'] ?? '',
            $destination['country'] ?? '',
        ]));
        if ($destAddress !== '') {
            $distanceKm = $this->distanceService->getDistanceInKm($destAddress);
        }

        $distanceSurcharge = 0.0;
        if ($distanceKm !== null && $distanceKm > 0) {
            $units = max(1, (int) ceil($distanceKm / 100));
            $distanceSurcharge = $units * 4.0;
        }

        $cost = ($base + ($weightKg * $perKg) + $zoneAddon + $distanceSurcharge) * $carrierMultiplier;
        $etaDays = $isInternational ? 5 : 2;
        if ($distanceKm !== null) {
            if ($distanceKm > 100) {
                ++$etaDays;
            }
            if ($distanceKm > 300) {
                ++$etaDays;
            }
        }

        return new ShippingQuote(
            cost: round($cost, 2),
            etaDays: $etaDays,
            carrier: strtoupper($carrier),
            trackingNumber: null,
            productCode: null,
        );
    }

    /**
     * Devis par poids (pour l'API). Même logique que quote() mais sans liste de produits.
     *
     * @param array{country?: string, postalCode?: string, city?: string, address?: string}|null $destination
     */
    public function quoteByWeight(
        float $weightKg,
        string $country = 'TN',
        string $carrier = 'POSTE',
        ?array $destination = null
    ): ShippingQuote {
        $weightKg = max(0.1, $weightKg);
        $destination = $destination ?? [
            'country' => $country,
            'postalCode' => '',
            'city' => '',
            'address' => '',
        ];
        $destination['country'] = $destination['country'] ?? $country;

        if (strtoupper($carrier) === 'DHL' && $this->dhlQuoteClient->isConfigured()) {
            $quote = $this->dhlQuoteClient->getQuote($weightKg, $destination);
            if ($quote !== null) {
                return $quote;
            }
        }

        if (strtoupper($carrier) === 'ARAMEX' && $this->aramexQuoteClient->isConfigured()) {
            $quote = $this->aramexQuoteClient->getQuote($weightKg, $destination);
            if ($quote !== null) {
                return $quote;
            }
        }

        $country = $country ?: 'TN';
        $isInternational = strtoupper($country) !== 'TN';
        $base = 5.0;
        $perKg = 2.0;
        $carrierMultiplier = match (strtoupper($carrier)) {
            'DHL' => 1.8,
            'ARAMEX' => 1.5,
            default => 1.0,
        };
        $zoneAddon = $isInternational ? 12.0 : 0.0;
        $destAddress = trim(implode(' ', [
            $destination['address'] ?? '',
            $destination['postalCode'] ?? '',
            $destination['city'] ?? '',
            $destination['country'] ?? '',
        ]));
        $distanceKm = $destAddress !== '' ? $this->distanceService->getDistanceInKm($destAddress) : null;
        $distanceSurcharge = 0.0;
        if ($distanceKm !== null && $distanceKm > 0) {
            $distanceSurcharge = max(1, (int) ceil($distanceKm / 100)) * 4.0;
        }
        $cost = ($base + ($weightKg * $perKg) + $zoneAddon + $distanceSurcharge) * $carrierMultiplier;
        $etaDays = $isInternational ? 5 : 2;
        if ($distanceKm !== null) {
            if ($distanceKm > 100) {
                ++$etaDays;
            }
            if ($distanceKm > 300) {
                ++$etaDays;
            }
        }
        return new ShippingQuote(
            cost: round($cost, 2),
            etaDays: $etaDays,
            carrier: strtoupper($carrier),
            trackingNumber: null,
            productCode: null,
        );
    }
}
