<?php

namespace App\Service\PaymentProcessor;

use App\Enum\PaymentProcessor;
use Exception;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

readonly class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(private PaypalPaymentProcessor $paypalPaymentProcessor)
    {
    }

    public function supports(PaymentProcessor $processor): bool
    {
        return $processor == PaymentProcessor::PAYPAL;
    }

    public function processPayment(float $price): bool
    {
        try {
            $smallestUnitPrice = (int)($price * 100);
            $this->paypalPaymentProcessor->pay($smallestUnitPrice);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}