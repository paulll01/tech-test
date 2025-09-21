<?php

namespace App\Exceptions\Booking;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OwnershipMismatchException extends UnprocessableEntityHttpException
{
    public function __construct(
        private bool $refOk,
        private bool $emailOk,
        private bool $regOk,
        string $message = 'Ownership verification failed.'
    ) {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        $errors = [];
        if (! $this->refOk) {
            $errors['unique_reference'][] = 'Invalid reference.';
        }
        if (! $this->emailOk) {
            $errors['customer_email'][] = 'Email does not match booking.';
        }
        if (! $this->regOk) {
            $errors['vehicle_reg'][] = 'Vehicle reg does not match booking.';
        }

        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
