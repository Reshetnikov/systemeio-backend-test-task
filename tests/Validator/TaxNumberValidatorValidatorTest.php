<?php
namespace App\Tests\Validator;

use App\Entity\TaxFormat;
use App\Validator\TaxNumberValidator;
use App\Validator\TaxNumberValidatorValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TaxNumberValidatorValidatorTest extends TestCase
{
    private TaxNumberValidatorValidator $validator;
    private EntityManagerInterface & \PHPUnit\Framework\MockObject\MockObject $entityManager;
    private ExecutionContextInterface & \PHPUnit\Framework\MockObject\MockObject $context;
    private EntityRepository & \PHPUnit\Framework\MockObject\MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->validator = new TaxNumberValidatorValidator($this->entityManager);
        $this->validator->initialize($this->context);
    }

    public function testValidateShouldSkipNullOrEmptyValue(): void
    {
        $constraint = new TaxNumberValidator();

        $this->entityManager->expects($this->never())
            ->method('getRepository');

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(null, $constraint);
        $this->validator->validate('', $constraint);
    }

    public function testValidateShouldFailWhenNoTaxFormatFound(): void
    {
        $taxNumber = 'DE123456789';
        $constraint = new TaxNumberValidator();

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(TaxFormat::class)
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'DE'])
            ->willReturn(null);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(function($param, $value) use ($violationBuilder, $taxNumber) {
                if ($param === '{{ value }}') {
                    $this->assertEquals($taxNumber, $value);
                }
                if ($param === '{{ country }}') {
                    $this->assertEquals('DE', $value);
                }
                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->validator->validate($taxNumber, $constraint);
    }

    public function testValidateShouldFailWhenRegexPatternDoesNotMatch(): void
    {
        $taxNumber = 'DE123';
        $constraint = new TaxNumberValidator();

        $taxFormat = $this->createMock(TaxFormat::class);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(TaxFormat::class)
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'DE'])
            ->willReturn($taxFormat);

        $taxFormat->expects($this->once())
            ->method('getRegexPattern')
            ->willReturn('^DE\d{9}$');

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(function($param, $value) use ($violationBuilder, $taxNumber) {
                if ($param === '{{ value }}') {
                    $this->assertEquals($taxNumber, $value);
                }
                if ($param === '{{ country }}') {
                    $this->assertEquals('DE', $value);
                }
                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->validator->validate($taxNumber, $constraint);
    }

    public function testValidateShouldPassWhenTaxNumberIsValid(): void
    {
        $taxNumber = 'DE123456789';
        $constraint = new TaxNumberValidator();

        $taxFormat = $this->createMock(TaxFormat::class);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(TaxFormat::class)
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'DE'])
            ->willReturn($taxFormat);

        $taxFormat->expects($this->once())
            ->method('getRegexPattern')
            ->willReturn('^DE\d{9}$');

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($taxNumber, $constraint);
    }
}