<?php

declare(strict_types=1);

namespace App\Actions\V1;

use App\Actions\V1\Action;
use App\Models\User;
use App\Support\ActionResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\ValidationErrorException;
use App\Services\V1\UserService;

class ExampleAction extends Action
{


    public function __construct(
        private UserService $userService
    ) {
        // UserService injected via dependency injection
    }

    /**
     * Create a new user example
     *
     * @param array|object $data
     * @return ActionResult
     */
    public function handle($data): ActionResult
    {

        // Validate permissions
        $this->validatePermissions([
            //'users.create' // Uncomment if you have permissions system
        ]);

        // Validate input data
        $validated = $this->validateData($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe tener un formato válido',
            'email.unique' => 'Este email ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        // Business logic with transaction
        return DB::transaction(function () use ($validated) {
            // Create user
            $user = $this->userService->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(), // For example purposes
            ]);

            // Return successful result with created user
            return $this->successResult(
                data: $user->only(['id', 'name', 'email', 'created_at']),
                message: 'Usuario creado exitosamente',
                statusCode: 201
            );
        });
    }

}
