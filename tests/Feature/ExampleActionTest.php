<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\V1\ExampleAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleActionTest extends TestCase
{
    use RefreshDatabase;

    private ExampleAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ExampleAction();
    }

    /** @test */
    public function it_creates_user_successfully()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->action->execute($data);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Usuario creado exitosamente', $result->getMessage());

        $userData = $result->getData();
        $this->assertArrayHasKey('id', $userData);
        $this->assertEquals('John Doe', $userData['name']);
        $this->assertEquals('john@example.com', $userData['email']);

        // Verify user exists in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_returns_validation_error_for_missing_fields()
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
        ];

        $result = $this->action->execute($data);

        $this->assertTrue($result->isError());
        $this->assertEquals(422, $result->getStatusCode());
        $this->assertArrayHasKey('name', $result->getErrors());
        $this->assertArrayHasKey('email', $result->getErrors());
        $this->assertArrayHasKey('password', $result->getErrors());
    }

    /** @test */
    public function it_returns_error_for_duplicate_email()
    {
        // Create existing user
        User::factory()->create(['email' => 'john@example.com']);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->action->execute($data);

        $this->assertTrue($result->isError());
        $this->assertEquals(422, $result->getStatusCode());
        $this->assertArrayHasKey('email', $result->getErrors());
    }

    /** @test */
    public function it_updates_user_successfully()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $data = [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $result = $this->action->update($data);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Usuario actualizado exitosamente', $result->getMessage());

        $userData = $result->getData();
        $this->assertEquals('Updated Name', $userData['name']);
        $this->assertEquals('updated@example.com', $userData['email']);
    }

    /** @test */
    public function it_checks_email_availability_successfully()
    {
        $data = ['email' => 'available@example.com'];

        $result = $this->action->checkEmail($data);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Email disponible', $result->getMessage());

        $responseData = $result->getData();
        $this->assertTrue($responseData['available']);
    }

    /** @test */
    public function it_returns_conflict_for_existing_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $data = ['email' => 'existing@example.com'];

        $result = $this->action->checkEmail($data);

        $this->assertTrue($result->isError());
        $this->assertEquals(409, $result->getStatusCode());
        $this->assertEquals('Este email ya está registrado', $result->getMessage());
        $this->assertArrayHasKey('email', $result->getErrors());
    }

    /** @test */
    public function action_result_to_array_works_correctly()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $result = $this->action->execute($data);
        $array = $result->toArray();

        $this->assertArrayHasKey('success', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('errors', $array);
        $this->assertArrayHasKey('status_code', $array);

        $this->assertTrue($array['success']);
        $this->assertEquals(201, $array['status_code']);
    }
}
