<?php

namespace App\Service\Shipping;

final class ShippingQuote
{
    public function __construct(
        public readonly float $cost,
        public readonly int $etaDays,
        public readonly string $carrier,
        public readonly ?string $trackingNumber = null,
        public readonly ?string $productCode = null,
    ) {
    }
}

