<?php

namespace Tests\Entity;

use App\Entity\Coupon;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    public function testGetId(): void
    {
        $coupon = new Coupon();

        $reflection = new \ReflectionClass($coupon);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($coupon, 123);

        $this->assertEquals(123, $coupon->getId());
    }
    
    public function testSetAndGetCode(): void
    {
        $coupon = new Coupon();
        $coupon->setCode('DISCOUNT50');
        
        $this->assertEquals('DISCOUNT50', $coupon->getCode());
    }

    public function testSetAndGetType(): void
    {
        $coupon = new Coupon();
        $coupon->setType('fixed');
        
        $this->assertEquals('fixed', $coupon->getType());
    }

    public function testSetAndGetValue(): void
    {
        $coupon = new Coupon();
        $coupon->setValue(50.00);
        
        $this->assertEquals(50.00, $coupon->getValue());
    }

    public function testApplyDiscountWithFixedType(): void
    {
        $coupon = new Coupon();
        $coupon->setType('fixed');
        $coupon->setValue(30.00);
        
        $price = 100.00;
        $discountedPrice = $coupon->applyDiscount($price);

        $this->assertEquals(70.00, $discountedPrice);
    }

    public function testApplyDiscountWithFixedTypePriceBelowValue(): void
    {
        $coupon = new Coupon();
        $coupon->setType('fixed');
        $coupon->setValue(150.00);
        
        $price = 100.00;
        $discountedPrice = $coupon->applyDiscount($price);

        $this->assertEquals(0.00, $discountedPrice);
    }

    public function testApplyDiscountWithPercentType(): void
    {
        $coupon = new Coupon();
        $coupon->setType('percent');
        $coupon->setValue(20.00);
        
        $price = 200.00;
        $discountedPrice = $coupon->applyDiscount($price);

        $this->assertEquals(160.00, $discountedPrice);
    }

    public function testApplyDiscountWithPercentTypeZeroValue(): void
    {
        $coupon = new Coupon();
        $coupon->setType('percent');
        $coupon->setValue(0.00);
        
        $price = 150.00;
        $discountedPrice = $coupon->applyDiscount($price);

        $this->assertEquals(150.00, $discountedPrice);
    }
}
