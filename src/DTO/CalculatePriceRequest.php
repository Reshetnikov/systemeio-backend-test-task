<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\TaxNumberValidator;

class CalculatePriceRequest
{
    #[Assert\NotBlank]
    public ?int $product;

    #[Assert\NotBlank]
    #[Assert\Length(min: 11)]
    #[TaxNumberValidator]
    public ?string $taxNumber;

    #[Assert\Length(max: 20)]
    public ?string $couponCode = null;

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