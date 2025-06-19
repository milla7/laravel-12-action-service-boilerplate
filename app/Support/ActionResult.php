<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ActionResult
{
    public function __construct(
        private bool $success,
        private mixed $data = null,
        private string $message = '',
        private int $statusCode = 200,
        private array $errors = []
    ) {}

    /**
     * Create a successful result
     */
    public static function success(
        mixed $data = null,
        string $message = 'Operación exitosa',
        int $statusCode = 200
    ): self {
        return new self(
            success: true,
            data: $data,
            message: $message,
            statusCode: $statusCode
        );
    }

    /**
     * Create an error result
     */
    public static function error(
        string $message = 'Ha ocurrido un error',
        array $errors = [],
        int $statusCode = 400,
        mixed $data = null
    ): self {
        return new self(
            success: false,
            data: $data,
            message: $message,
            statusCode: $statusCode,
            errors: $errors
        );
    }

    /**
     * Create a validation error result
     */
    public static function validationError(
        array $errors,
        string $message = 'Error de validación'
    ): self {
        return new self(
            success: false,
            message: $message,
            statusCode: 422,
            errors: $errors
        );
    }

    /**
     * Convert to API JSON response
     */
    public function toApiResponse(): JsonResponse
    {
        return response()->json([
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
        ], $this->statusCode);
    }

    /**
     * Get data structure for Livewire components
     */
    public function toLivewireData(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
        ];
    }

    /**
     * Convert to flash data for web redirects
     */
    public function toFlashData(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'errors' => $this->errors,
        ];
    }

    // Getters
    public function isSuccess(): bool { return $this->success; }
    public function isError(): bool { return !$this->success; }
    public function getData(): mixed { return $this->data; }
    public function getMessage(): string { return $this->message; }
    public function getErrors(): array { return $this->errors; }
    public function getStatusCode(): int { return $this->statusCode; }

    /**
     * Convert to array (useful for testing)
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
            'status_code' => $this->statusCode,
        ];
    }
}
