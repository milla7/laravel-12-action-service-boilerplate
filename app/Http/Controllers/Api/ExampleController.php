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

}
