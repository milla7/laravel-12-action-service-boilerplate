<?php

declare(strict_types=1);

namespace App\Actions\V1;

use App\Exceptions\ValidationErrorException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\Validator;

abstract class Action
{
    /**
     * Execute the action with the given data
     *
     * @param array|object $data
     * @return mixed
     */
    abstract public function execute($data);

    /**
     * Validate the provided data against the given rules
     *
     * @param array|object $data
     * @param array $rules
     * @param array $messages
     * @return array
     * @throws ValidationErrorException
     */
    protected function validateData($data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationErrorException(
                "Error de validación de datos: " . json_encode($validator->errors()),
                config('app.errors_code.validation'),
                $validator->errors()
            );
        }

        return $validator->validated();
    }

    /**
     * Validate user permissions for the action
     *
     * @param array $permissions
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function validatePermissions(array $permissions): void
    {
        if (!auth()->check()) {
            abort(401, 'Usuario no autenticado');
        }

        foreach ($permissions as $permission) {
            if (!auth()->user()->can($permission)) {
                abort(403, 'No tienes permisos necesarios para realizar esta acción');
            }
        }
    }

    /**
     * Check if action logging is enabled
     *
     * @return bool
     */
    protected function isActionLoggingEnabled(): bool
    {
        return config('app.logs_actions_enabled', false);
    }
}
