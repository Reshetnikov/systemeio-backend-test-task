<?php

namespace App\Service\PaymentProcessor;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    private PaypalPaymentProcessor $paypalPaymentProcessor;

    public function __construct(PaypalPaymentProcessor $paypalPaymentProcessor)
    {
        $this->paypalPaymentProcessor = $paypalPaymentProcessor;
    }

    public function supports(string $processor): bool
    {
        return $processor === 'paypal';
    }

    public function processPayment(float $price): bool
    {
        try {
            $smallestUnitPrice = (int)($price * 100);
            $this->paypalPaymentProcessor->pay($smallestUnitPrice);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}