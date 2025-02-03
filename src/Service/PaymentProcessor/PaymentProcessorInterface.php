<?php

namespace App\Service\PaymentProcessor;

use App\Enum\PaymentProcessor;

interface PaymentProcessorInterface
{
    public function supports(PaymentProcessor $processor): bool;
    public function processPayment(float $price): bool;
}