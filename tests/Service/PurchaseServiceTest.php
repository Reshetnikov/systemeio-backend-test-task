<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use App\Entity\TaxFormat;
use App\Enum\PaymentProcessor;
use App\Exception\ValidationException;
use App\Service\PurchaseService;
use App\Service\PaymentProcessor\PaymentProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PurchaseServiceTest extends TestCase
{
    private EntityManagerInterface & MockObject $entityManager;
    private PurchaseService $purchaseService;
    private array $paymentProcessors;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $paypalProcessor = $this->createMock(PaymentProcessorInterface::class);
        $paypalProcessor->method('supports')->willReturnCallback(fn(PaymentProcessor $processor) => $processor == PaymentProcessor::PAYPAL);
        $paypalProcessor->method('processPayment')->willReturn(true);

        $stripeProcessor = $this->createMock(PaymentProcessorInterface::class);
        $stripeProcessor->method('supports')->willReturnCallback(fn(PaymentProcessor $processor) => $processor == PaymentProcessor::STRIPE);
        $stripeProcessor->method('processPayment')->willReturn(true);

        $this->paymentProcessors = [$paypalProcessor, $stripeProcessor];

        $this->purchaseService = new PurchaseService($this->entityManager, $this->paymentProcessors);
    }

    public function testCalculatePriceWithValidData(): void
    {
        $product = new Product();
        $product->setName('Iphone')->setPrice(100.00);

        $coupon = new Coupon();
        $coupon->setCode('P10')->setType('percent')->setValue(10);

        $taxFormat = new TaxFormat();
        $taxFormat->setCountryCode('DE')->setTaxRate(19.00);

        $productRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $productRepositoryMock->method('find')->willReturn($product);

        $couponRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $couponRepositoryMock->method('findOneBy')->willReturn($coupon);

        $taxFormatRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $taxFormatRepositoryMock->method('findOneBy')->willReturn($taxFormat);

        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Product::class, $productRepositoryMock],
                [Coupon::class, $couponRepositoryMock],
                [TaxFormat::class, $taxFormatRepositoryMock],
            ]);

        $price = $this->purchaseService->calculatePrice(1, 'P10', 'DE123456789');
        $this->assertEquals(107.10, $price); // 100 - 10% + 19% налога
    }

    public function testCalculatePriceThrowsExceptionForInvalidProduct(): void
    {
        $productRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $productRepositoryMock->method('find')->willReturn(null);


        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Product::class, $productRepositoryMock],
            ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Product not found.');

        $this->purchaseService->calculatePrice(999, null, 'DE123456789');
    }

    public function testCalculatePriceThrowsExceptionForInvalidCoupon(): void
    {
        $product = new Product();
        $product->setName('Iphone')->setPrice(100.00);

        $productRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $productRepositoryMock->method('find')->willReturn($product);

        $couponRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $couponRepositoryMock->method('findOneBy')->willReturn(null);

        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Product::class, $productRepositoryMock],
                [Coupon::class, $couponRepositoryMock],
            ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid coupon code.');

        $this->purchaseService->calculatePrice(1, 'abc', 'DE123456789');
    }

    public function testCalculatePriceThrowsExceptionForInvalidTax(): void
    {
        $product = new Product();
        $product->setName('Iphone')->setPrice(100.00);

        $coupon = new Coupon();
        $coupon->setCode('P10')->setType('percent')->setValue(10);

        $productRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $productRepositoryMock->method('find')->willReturn($product);

        $couponRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $couponRepositoryMock->method('findOneBy')->willReturn($coupon);

        $taxFormatRepositoryMock = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $taxFormatRepositoryMock->method('findOneBy')->willReturn(null);

        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Product::class, $productRepositoryMock],
                [Coupon::class, $couponRepositoryMock],
                [TaxFormat::class, $taxFormatRepositoryMock],
            ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Tax number/');

        $this->purchaseService->calculatePrice(999, null, 'DE123456789');
    }

    public function testProcessPaymentWithValidProcessor(): void
    {
        $this->purchaseService->processPayment(PaymentProcessor::PAYPAL, 150.00);

        $this->addToAssertionCount(1);
    }

    public function testProcessPaymentThrowsExceptionForInvalidProcessor(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unsupported payment processor.');

        $this->purchaseService->processPayment(PaymentProcessor::UNKNOWN, 150.00);
    }

    public function testProcessPaymentThrowsExceptionOnPaymentFailure(): void
    {
        $failingProcessor = $this->createMock(PaymentProcessorInterface::class);
        $failingProcessor->method('supports')->willReturn(true);
        $failingProcessor->method('processPayment')->willReturn(false);

        $purchaseService = new PurchaseService($this->entityManager, [$failingProcessor]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('payment failed.');

        $purchaseService->processPayment(PaymentProcessor::UNKNOWN, 150.00);
    }
}
