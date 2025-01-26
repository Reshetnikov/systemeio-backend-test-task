<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ValidationException extends \RuntimeException
{
    private string $field;

    public function __construct(string $field, string $message, int $code = Response::HTTP_BAD_REQUEST, \Throwable $previous = null)
    {
        $this->field = $field;
        parent::__construct($message, $code, $previous);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'message' => $this->getMessage(),
        ];
    }
}
