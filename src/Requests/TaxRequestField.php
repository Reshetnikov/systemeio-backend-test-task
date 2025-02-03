<?php
namespace App\Requests;

use App\Validator\TaxNumberValidator;
use Symfony\Component\Validator\Constraints as Assert;

trait TaxRequestField
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 11)]
    #[TaxNumberValidator]
    public ?string $taxNumber;
}