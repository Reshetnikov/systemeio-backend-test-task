<?php
namespace App\Requests;

use App\Enum\PaymentProcessor;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest
{
    use ProductRequestField, TaxRequestField, CouponRequestField;

    #[Assert\NotBlank]
    #[Assert\Type(PaymentProcessor::class)]
    public ?PaymentProcessor $paymentProcessor;

    public function __construct(
        ?int $product,
        ?string $taxNumber,
        ?string $paymentProcessor,
        ?string $couponCode = null
    ) {
        $this->product = $product;
        $this->taxNumber = $taxNumber;
        $this->paymentProcessor = PaymentProcessor::tryFrom($paymentProcessor);
        $this->couponCode = $couponCode;
    }
}