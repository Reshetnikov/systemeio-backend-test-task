<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use App\Entity\TaxFormat;
use App\Enum\PaymentProcessor;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;

readonly class PurchaseService
{
    public function __construct(private EntityManagerInterface $entityManager, private iterable $paymentProcessors)
    {
    }

    public function calculatePrice(int $productId, ?string $couponCode, string $taxNumber): float 
    {
        $product = $this->entityManager->getRepository(Product::class)->find($productId);
        if (!$product) {
            throw new ValidationException('product', 'Product not found.');
        }

        $coupon = null;
        if ($couponCode) {
            $coupon = $this->entityManager->getRepository(Coupon::class)
                ->findOneBy(['code' => $couponCode]);

            if (!$coupon) {
                throw new ValidationException('couponCode', 'Invalid coupon code.');
            }
        }

        $price = $product->getPrice();
        if ($coupon) {
            $price = $coupon->applyDiscount($price);
        }

        $taxFormat = $this->entityManager->getRepository(TaxFormat::class)
            ->findOneBy(['countryCode' => substr($taxNumber, 0, 2)]);

        if (!$taxFormat) {
            throw new ValidationException('taxNumber', sprintf('Tax number "%s" is invalid for country %s.', $taxNumber, substr($taxNumber, 0, 2)));
        }

        return $taxFormat->applyTax($price);
    }

    public function processPayment(PaymentProcessor $processor, float $price): void
    {
        foreach ($this->paymentProcessors as $paymentProcessor) {
            if ($paymentProcessor->supports($processor)) {
                if (!$paymentProcessor->processPayment($price)) {
                    throw new ValidationException('paymentProcessor', "{$processor->value} payment failed.");
                }
                return;
            }
        }
        throw new ValidationException('paymentProcessor', 'Unsupported payment processor.');
    }
}