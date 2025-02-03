<?php
namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;

trait ProductRequestField
{
    #[Assert\NotBlank]
    #[Assert\Type("integer")]
    public ?int $product;
}