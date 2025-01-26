<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Exception\ValidationException;
use App\Service\PurchaseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PurchaseController
{
    private PurchaseService $purchaseService;

    public function __construct( PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function calculatePrice(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $purchaseRequest = new CalculatePriceRequest(
            product: $data['product'] ?? null,
            taxNumber: $data['taxNumber'] ?? null,
            couponCode: $data['couponCode'] ?? null
        );

        $violations = $validator->validate($purchaseRequest);

        if (count($violations) > 0) {
            return $this->createErrorResponse($violations);
        }

        try {
            $price = $this->purchaseService->calculatePrice($purchaseRequest->product, $purchaseRequest->couponCode, $purchaseRequest->taxNumber);
            return new JsonResponse([
                'price' => $price,
            ]);
        } catch (ValidationException $e) {
            return $this->createErrorResponse($e);
        }
    }

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $purchaseRequest = new PurchaseRequest(
            product: $data['product'] ?? null,
            taxNumber: $data['taxNumber'] ?? null,
            paymentProcessor: $data['paymentProcessor'] ?? null,
            couponCode: $data['couponCode'] ?? null
        );

        $violations = $validator->validate($purchaseRequest);

        if (count($violations) > 0) {
            return $this->createErrorResponse($violations);
        }

        try {
            $price = $this->purchaseService->calculatePrice($purchaseRequest->product, $purchaseRequest->couponCode, $purchaseRequest->taxNumber);
            $this->purchaseService->processPayment($purchaseRequest->paymentProcessor, $price);
            return new JsonResponse([
                'price' => $price,
            ]);
        } catch (ValidationException $e) {
            return $this->createErrorResponse($e);
        }
    }

    private function createErrorResponse($violations): JsonResponse
    {
        $errors = [];
        if ($violations instanceof \Symfony\Component\Validator\ConstraintViolationListInterface) {
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(), // Поле, к которому относится ошибка
                    'message' => $violation->getMessage(),    // Сообщение об ошибке
                ];
            }
        }
        elseif ($violations instanceof \App\Exception\ValidationException) {
            $errors[] = [
                'field' => $violations->getField(),
                'message' => $violations->getMessage(),
            ];
        }
        return new JsonResponse(['errors' => $errors], 400);
    }
}