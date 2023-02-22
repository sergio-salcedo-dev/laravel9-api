<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    public function test_user_cannot_register_without_name_email_and_password(): void
    {
        $this->postJson(route('user.register'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password'])
            ->json();
    }

    public function test_user_cannot_register_without_confirming_password(): void
    {
        $request = [
            'name' => 'user',
            'email' => 'user@test.com',
            'password' => 'password',
        ];

        $this->postJson(route('user.register', $request))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password'])
            ->assertJsonMissingValidationErrors(['name', 'email'])
            ->json();
    }

    public function test_user_cannot_register_without_a_valid_email(): void
    {
        $request = [
            'name' => 'user',
            'email' => 'invalid_email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->postJson(route('user.register', $request))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonMissingValidationErrors(['name', 'password'])
            ->json();
    }

    public function test_user_can_register_when_meets_validation_rules(): void
    {
        $request = [
            'name' => 'user',
            'email' => 'user@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->postJson(route('user.register', $request))
            ->assertCreated()
            ->assertJsonMissingValidationErrors((['name', 'email', 'password']))
            ->json();

        $this->assertDatabaseHas(User::class, ['email' => 'user@test.com']);
    }

    public function test_user_cannot_register_if_emails_exists_in_database(): void
    {
        $request = [
            'name' => 'user',
            'email' => 'user@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        User::factory()->create(['email' => 'user@test.com']);

        $this->postJson(route('user.register', $request))
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors((['name', 'password']))
            ->assertJsonValidationErrors(('email'))
            ->json();

        $this->assertDatabaseCount(User::class, 1);
    }
}
