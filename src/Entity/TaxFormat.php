<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tax_formats')]
class TaxFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 2, unique: true)]
    private string $countryCode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $regexPattern;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private float $taxRate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getRegexPattern(): string
    {
        return $this->regexPattern;
    }

    public function setRegexPattern(string $regexPattern): self
    {
        $this->regexPattern = $regexPattern;
        return $this;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $taxRate): self
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    public function applyTax(float $price): float
    {
        return $price + $price * $this->taxRate / 100;
    }
}