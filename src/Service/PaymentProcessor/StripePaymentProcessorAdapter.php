<?php

namespace App\Service\PaymentProcessor;

use App\Enum\PaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

readonly class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(private StripePaymentProcessor $stripePaymentProcessor)
    {
    }

    public function supports(PaymentProcessor $processor): bool
    {
        return $processor == PaymentProcessor::STRIPE;
    }
    
    public function processPayment(float $price): bool
    {
        return $this->stripePaymentProcessor->processPayment($price);
    }
}