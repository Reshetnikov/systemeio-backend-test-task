<?php

namespace App\Service\PaymentProcessor;

interface PaymentProcessorInterface
{
    public function supports(string $processor): bool;
    public function processPayment(float $price): bool;
}