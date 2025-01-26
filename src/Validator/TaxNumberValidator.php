<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TaxNumberValidator extends Constraint
{
    public string $message = 'Tax number "{{ value }}" is invalid for country {{ country }}.';
}