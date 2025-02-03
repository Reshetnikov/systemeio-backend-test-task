<?php

namespace Tests\Entity;

use App\Entity\TaxFormat;
use PHPUnit\Framework\TestCase;

class TaxFormatTest extends TestCase
{
    public function testSetAndGetCountryCode(): void
    {
        $taxFormat = new TaxFormat();
        $taxFormat->setCountryCode('US');

        $this->assertEquals('US', $taxFormat->getCountryCode());
    }

    public function testSetAndGetRegexPattern(): void
    {
        $taxFormat = new TaxFormat();
        $taxFormat->setRegexPattern('/^[A-Z]{2}$/');

        $this->assertEquals('/^[A-Z]{2}$/', $taxFormat->getRegexPattern());
    }

    public function testSetAndGetTaxRate(): void
    {
        $taxFormat = new TaxFormat();
        $taxFormat->setTaxRate(20.00);

        $this->assertEquals(20.00, $taxFormat->getTaxRate());
    }

    public function testGetId(): void
    {
        $taxFormat = new TaxFormat();
        $reflection = new \ReflectionClass($taxFormat);
        $property = $reflection->getProperty('id');
        // $property->setAccessible(true); // for php < 8.1
        $property->setValue($taxFormat, 42);

        $this->assertEquals(42, $taxFormat->getId());
    }

    public function testApplyTax(): void
    {
        $taxFormat = new TaxFormat();
        $taxFormat->setTaxRate(20.00);

        $price = 100.00;
        $expectedPriceWithTax = 120.00;

        $this->assertEquals($expectedPriceWithTax, $taxFormat->applyTax($price));
    }

    public function testApplyTaxWithZeroRate(): void
    {
        $taxFormat = new TaxFormat();
        $taxFormat->setTaxRate(0.00);

        $price = 100.00;
        $expectedPriceWithTax = 100.00;

        $this->assertEquals($expectedPriceWithTax, $taxFormat->applyTax($price));
    }
}
