<?php

namespace App\Service\PaymentProcessor;

use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    private StripePaymentProcessor $stripePaymentProcessor;

    public function __construct(StripePaymentProcessor $stripePaymentProcessor)
    {
        $this->stripePaymentProcessor = $stripePaymentProcessor;
    }

    public function supports(string $processor): bool
    {
        return $processor === 'stripe';
    }
    
    public function processPayment(float $price): bool
    {
        return $this->stripePaymentProcessor->processPayment($price);
    }
}