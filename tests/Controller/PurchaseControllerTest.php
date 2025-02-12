<?php

namespace App\Tests\Controller;

use App\Enum\PaymentProcessor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PurchaseControllerTest extends WebTestCase
{
    public function testCalculatePriceSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', '/calculate-price', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'P10',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('price', $data);
        $this->assertGreaterThan(0, $data['price']);
    }

    public function testCalculatePriceEmptyBody(): void
    {
        $client = static::createClient();
        $client->request('POST', '/calculate-price', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));

        $expectedErrors = [
            [
                'field' => 'product',
                'message' => "This value should not be blank.",
            ],
            [
                'field' => 'taxNumber',
                'message' => "This value should not be blank.",
            ]
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testCalculatePriceValidationErrors(): void
    {
        $client = static::createClient();
        $client->request('POST', '/calculate-price', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => null,
            'taxNumber' => 'DE123',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));

        $expectedErrors = [
            [
                'field' => 'product',
                'message' => 'This value should not be blank.',
            ],
            [
                'field' => 'taxNumber',
                'message' => 'This value is too short. It should have 11 characters or more.',
            ],
            [
                'field' => 'taxNumber',
                'message' => 'Tax number "DE123" is invalid for country DE.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testCalculatePriceWrongCoupon(): void
    {
        $client = static::createClient();
        $client->request('POST', '/calculate-price', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'wrongCoupon',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));

        $expectedErrors = [
            [
                'field' => 'couponCode',
                'message' => 'Invalid coupon code.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testCalculatePriceWrongProduct(): void
    {
        $client = static::createClient();
        $client->request('POST', '/calculate-price', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 111,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));

        $expectedErrors = [
            [
                'field' => 'product',
                'message' => 'Product not found.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testPurchaseSuccessPaypal(): void
    {
        $client = static::createClient();
        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => PaymentProcessor::PAYPAL->value,
            'couponCode' => 'P10',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('price', $data);
        $this->assertGreaterThan(0, $data['price']);
    }

    public function testPurchaseSuccessStripe(): void
    {
        $client = static::createClient();
        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => PaymentProcessor::STRIPE->value,
            'couponCode' => 'P10',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('price', $data);
        $this->assertGreaterThan(0, $data['price']);
    }

    public function testPurchaseWrongPaymentProcessor(): void
    {
        $client = static::createClient();
        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => 'wrongPaymentProcessor',
            'couponCode' => 'P10',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));

        $expectedErrors = [
            [
                'field' => 'paymentProcessor',
                'message' => 'This value should not be blank.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testPurchaseUnknownPaymentProcessor(): void
    {
        $client = static::createClient();
        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => PaymentProcessor::UNKNOWN,
            'couponCode' => 'P10',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));

        $expectedErrors = [
            [
                'field' => 'paymentProcessor',
                'message' => 'Unsupported payment processor.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testPurchaseWrongProduct(): void
    {
        $client = static::createClient();
        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 111,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => PaymentProcessor::STRIPE->value,
            'couponCode' => 'P10',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));

        $expectedErrors = [
            [
                'field' => 'product',
                'message' => 'Product not found.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testPurchaseWrongTaxCountry(): void
    {
        $client = static::createClient();
        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'AA123456789',
            'paymentProcessor' => PaymentProcessor::STRIPE->value,
            'couponCode' => 'P10',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));
        $expectedErrors = [
            [
                'field' => 'taxNumber',
                'message' => 'Tax number "AA123456789" is invalid for country AA.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }

    public function testPurchaseWrongTaxRegex(): void
    {
        $client = static::createClient();
        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE12345678A', // ...A 
            'paymentProcessor' => PaymentProcessor::STRIPE->value,
            'couponCode' => 'P10',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertGreaterThan(0, count($data['errors']));
        $expectedErrors = [
            [
                'field' => 'taxNumber',
                'message' => 'Tax number "DE12345678A" is invalid for country DE.',
            ],
        ];

        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $data['errors'], sprintf(
                'Expected error for field "%s" with message "%s" not found.',
                $expectedError['field'],
                $expectedError['message']
            ));
        }
    }
}
