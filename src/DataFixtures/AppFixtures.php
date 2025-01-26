<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Entity\TaxFormat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $connection = $manager->getConnection();

        // Очистка таблиц с RESTART IDENTITY
        $this->truncateWithRestart($connection, 'products');
        $this->truncateWithRestart($connection, 'coupons');
        $this->truncateWithRestart($connection, 'tax_formats');
        $products = [
            ['Iphone', 100.00],
            ['Наушники', 20.00],
            ['Чехол', 10.00],
        ];

        foreach ($products as [$name, $price]) {
            $product = new Product();
            $product->setName($name)->setPrice($price);
            $manager->persist($product);
        }

        $coupons = [
            ['P10', 'percent', 10],
            ['P100', 'fixed', 100],
            ['D15', 'percent', 15],
        ];

        foreach ($coupons as [$code, $type, $value]) {
            $coupon = new Coupon();
            $coupon->setCode($code)->setType($type)->setValue($value);
            $manager->persist($coupon);
        }

        // Налоговые форматы
        $taxFormats = [
            ['DE', '^DE\\d{9}$', 19.00],
            ['IT', '^IT\\d{11}$', 22.00],
            ['GR', '^GR\\d{9}$', 24.00],
            ['FR', '^FR[A-Z]{2}\\d{9}$', 20.00],
        ];

        foreach ($taxFormats as [$countryCode, $regexPattern, $taxRate]) {
            $taxFormat = new TaxFormat();
            $taxFormat->setCountryCode($countryCode)
                ->setRegexPattern($regexPattern)
                ->setTaxRate($taxRate);
            $manager->persist($taxFormat);
        }

        $manager->flush();
    }

    private function truncateWithRestart(Connection $connection, string $tableName): void
    {
        $connection->executeStatement('TRUNCATE TABLE ' . $tableName . ' RESTART IDENTITY CASCADE');
    }
}
