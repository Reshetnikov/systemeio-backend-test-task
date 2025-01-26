<?php

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testConstructorAndGetField()
    {
        $field = 'username';
        $message = 'Username is required';
        $code = Response::HTTP_BAD_REQUEST;

        $exception = new ValidationException($field, $message, $code);

        $this->assertEquals($field, $exception->getField());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testToArray()
    {
        $field = 'email';
        $message = 'Invalid email format';
        $exception = new ValidationException($field, $message);

        $result = $exception->toArray();

        $this->assertArrayHasKey('field', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals($field, $result['field']);
        $this->assertEquals($message, $result['message']);
    }
}
