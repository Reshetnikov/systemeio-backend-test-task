<?php

namespace App\Tests\Service\PaymentProcessor;

use App\Enum\PaymentProcessor;
use App\Service\PaymentProcessor\StripePaymentProcessorAdapter;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;
use PHPUnit\Framework\TestCase;

class StripePaymentProcessorAdapterTest extends TestCase
{
    private StripePaymentProcessor $stripePaymentProcessorMock;
    private StripePaymentProcessorAdapter $adapter;

    protected function setUp(): void
    {
        $this->stripePaymentProcessorMock = $this->createMock(StripePaymentProcessor::class);
        $this->adapter = new StripePaymentProcessorAdapter($this->stripePaymentProcessorMock);
    }

    public function testSupportsReturnsTrueForStripe(): void
    {
        $this->assertTrue($this->adapter->supports(PaymentProcessor::STRIPE));
    }

    public function testSupportsReturnsFalseForOtherProcessor(): void
    {
        $this->assertFalse($this->adapter->supports(PaymentProcessor::PAYPAL));
    }

    public function testProcessPaymentReturnsTrueOnSuccessfulPayment(): void
    {
         /** @var StripePaymentProcessor|\PHPUnit\Framework\MockObject\MockObject $stripePaymentProcessorMock */
         $stripePaymentProcessorMock = $this->stripePaymentProcessorMock;
         $stripePaymentProcessorMock
            ->expects($this->once())
            ->method('processPayment')
            ->with($this->equalTo(100.00))
            ->willReturn(true);

        $result = $this->adapter->processPayment(100.00);

        $this->assertTrue($result);
    }

    public function testProcessPaymentReturnsFalseOnFailedPayment(): void
    {
         /** @var StripePaymentProcessor|\PHPUnit\Framework\MockObject\MockObject $stripePaymentProcessorMock */
         $stripePaymentProcessorMock = $this->stripePaymentProcessorMock;
         $stripePaymentProcessorMock
            ->expects($this->once())
            ->method('processPayment')
            ->with($this->equalTo(50.00))
            ->willReturn(false);

        $result = $this->adapter->processPayment(50.00);

        $this->assertFalse($result);
    }
}
