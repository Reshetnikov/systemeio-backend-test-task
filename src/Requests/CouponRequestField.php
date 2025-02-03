<?php
namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;

trait CouponRequestField
{
    #[Assert\Length(max: 20)]
    public ?string $couponCode = null;
}
