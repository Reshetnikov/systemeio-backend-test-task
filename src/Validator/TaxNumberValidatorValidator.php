<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\TaxFormat;

class TaxNumberValidatorValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        $countryCode = substr($value, 0, 2);
        $taxFormat = $this->entityManager->getRepository(TaxFormat::class)
            ->findOneBy(['countryCode' => $countryCode]);
  
        if (!$taxFormat || !preg_match('/' . $taxFormat->getRegexPattern() . '/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ country }}', $countryCode)
                ->addViolation();
        }
    }
}