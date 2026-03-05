<?php

namespace App\Tests\Service\Shipping;

use App\Entity\Produit;
use App\Service\Shipping\AramexQuoteClient;
use App\Service\Shipping\DhlQuoteClient;
use App\Service\Shipping\DistanceService;
use App\Service\Shipping\ShippingQuoteService;
use PHPUnit\Framework\TestCase;

final class ShippingQuoteServiceTest extends TestCase
{
    private function createService(float $distanceKm = 0): ShippingQuoteService
    {
        $dhl = $this->createMock(DhlQuoteClient::class);
        $aramex = $this->createMock(AramexQuoteClient::class);
        $distance = $this->createMock(DistanceService::class);
        $distance->method('getDistanceInKm')->willReturn($distanceKm > 0 ? $distanceKm : null);

        return new ShippingQuoteService($dhl, $aramex, $distance);
    }

    public function test_quote_poste_tunisie_retourne_cout_et_delai(): void
    {
        $service = $this->createService();

        $p = new Produit();
        $p->setPrix(10);
        $p->setPoidsKg(0.5);

        $quote = $service->quote([$p], 'TN', 'POSTE');

        self::assertSame('POSTE', $quote->carrier);
        self::assertSame(2, $quote->etaDays);
        self::assertGreaterThan(0, $quote->cost);
    }

    public function test_quote_international_ajoute_surcharge_zone(): void
    {
        $service = $this->createService();

        $p = new Produit();
        $p->setPrix(10);
        $p->setPoidsKg(1.0);

        $tnQuote = $service->quote([$p], 'TN', 'POSTE');
        $frQuote = $service->quote([$p], 'FR', 'POSTE');

        self::assertGreaterThan($tnQuote->cost, $frQuote->cost);
        self::assertSame(5, $frQuote->etaDays);
    }
}

