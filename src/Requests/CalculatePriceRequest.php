<?php
namespace App\Requests;

class CalculatePriceRequest
{
    use ProductRequestField, TaxRequestField, CouponRequestField;

    public function __construct(
        ?int $product,
        ?string $taxNumber,
        ?string $couponCode = null
    ) {
        $this->product = $product;
        $this->taxNumber = $taxNumber;
        $this->couponCode = $couponCode;
    }
}