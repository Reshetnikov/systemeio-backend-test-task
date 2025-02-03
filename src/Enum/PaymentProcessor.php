<?php

namespace App\Enum;

enum PaymentProcessor: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';

    case UNKNOWN = 'unknown';
}