<?php

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationException) {
            $response = new JsonResponse(
                ['errors' => [[
                    'field' => $exception->getField(),
                    'message' => $exception->getMessage(),
                ]]],
                400
            );
            $event->setResponse($response);
            return;
        }

        if ($exception instanceof HttpException
            && $exception->getPrevious() instanceof ValidationFailedException
        ) {
            $violations = $exception->getPrevious()->getViolations();

            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $response = new JsonResponse(
                ['errors' => $errors],
                400
            );

            $event->setResponse($response);
        }
    }
}