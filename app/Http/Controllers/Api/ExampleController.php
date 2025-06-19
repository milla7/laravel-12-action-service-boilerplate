<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\V1\ExampleAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExampleController extends Controller
{
    public function __construct(
        private ExampleAction $exampleAction
    ) {}

    /**
     * Handle API request using Action
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->exampleAction->execute($request->all());

        return $result->toApiResponse();
    }

    /**
     * Update user - calls the update method of ExampleAction
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->exampleAction->update([
            'id' => $id,
            ...$request->all()
        ]);

        return $result->toApiResponse();
    }

    /**
     * Check email availability
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $result = $this->exampleAction->checkEmail($request->all());

        return $result->toApiResponse();
    }
}
