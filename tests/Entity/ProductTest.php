<?php

namespace Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testSetAndGetName(): void
    {
        $product = new Product();
        $product->setName('Test Product');

        $this->assertEquals('Test Product', $product->getName());
    }

    public function testSetAndGetPrice(): void
    {
        $product = new Product();
        $product->setPrice(99.99);

        $this->assertEquals(99.99, $product->getPrice());
    }

    public function testGetId(): void
    {
        $product = new Product();
        $reflection = new \ReflectionClass($product);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($product, 1);

        $this->assertEquals(1, $product->getId());
    }
}
