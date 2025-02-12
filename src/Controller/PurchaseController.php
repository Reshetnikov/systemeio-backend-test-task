<?php

namespace App\Controller;

use App\Requests\CalculatePriceRequest;
use App\Requests\PurchaseRequest;
use App\Service\PurchaseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

readonly class PurchaseController
{
    public function __construct(private PurchaseService $purchaseService)
    {
    }

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function calculatePrice(
        #[MapRequestPayload] CalculatePriceRequest $calculatePriceRequest
    ): JsonResponse
    {
        $price = $this->purchaseService->calculatePrice(
            $calculatePriceRequest->product,
            $calculatePriceRequest->couponCode,
            $calculatePriceRequest->taxNumber
        );
        return new JsonResponse([
            'price' => $price,
        ]);
    }

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(
        #[MapRequestPayload] PurchaseRequest $purchaseRequest
    ): JsonResponse
    {
        $price = $this->purchaseService->calculatePrice(
            $purchaseRequest->product,
            $purchaseRequest->couponCode,
            $purchaseRequest->taxNumber
        );
        $this->purchaseService->processPayment($purchaseRequest->paymentProcessor, $price);
        return new JsonResponse([
            'price' => $price,
        ]);
    }
}