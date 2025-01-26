<?php

namespace Tests\Service\PaymentProcessor;

use App\Service\PaymentProcessor\PaypalPaymentProcessorAdapter;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use PHPUnit\Framework\TestCase;

class PaypalPaymentProcessorAdapterTest extends TestCase
{
    private PaypalPaymentProcessor $paypalPaymentProcessorMock;
    private PaypalPaymentProcessorAdapter $adapter;

    protected function setUp(): void
    {
        $this->paypalPaymentProcessorMock = $this->createMock(PaypalPaymentProcessor::class);
        $this->adapter = new PaypalPaymentProcessorAdapter($this->paypalPaymentProcessorMock);
    }

    public function testSupportsReturnsTrueForPaypal(): void
    {
        $this->assertTrue($this->adapter->supports('paypal'));
    }

    public function testSupportsReturnsFalseForOtherProcessor(): void
    {
        $this->assertFalse($this->adapter->supports('stripe'));
    }

    public function testProcessPaymentReturnsTrueOnSuccessfulPayment(): void
    {
        /** @var PaypalPaymentProcessor|\PHPUnit\Framework\MockObject\MockObject $paypalPaymentProcessorMock */
        $paypalPaymentProcessorMock = $this->paypalPaymentProcessorMock;
        $paypalPaymentProcessorMock
            ->expects($this->once())
            ->method('pay')
            ->with($this->equalTo(10000)); //100.00 * 100 = 10000

        $result = $this->adapter->processPayment(100.00);

        $this->assertTrue($result);
    }

    public function testProcessPaymentReturnsFalseOnException(): void
    {
        /** @var PaypalPaymentProcessor|\PHPUnit\Framework\MockObject\MockObject $paypalPaymentProcessorMock */
        $paypalPaymentProcessorMock = $this->paypalPaymentProcessorMock;
        $paypalPaymentProcessorMock
            ->expects($this->once())
            ->method('pay')
            ->with($this->equalTo(100001)) //1000.01 * 100 = 10000
            ->willThrowException(new \Exception('Payment failed'));

        $result = $this->adapter->processPayment(1000.01);

        $this->assertFalse($result);
    }
}
