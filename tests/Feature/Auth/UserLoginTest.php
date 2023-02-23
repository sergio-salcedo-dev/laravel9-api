<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Helpers\UserMessageHelper;
use App\Http\Controllers\Auth\LoginController;
use Tests\Helpers\UserTestHelper;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    public function test_user_cannot_login_without_valid_email(): void
    {
        $request = [
            'email' => 'invalid_email',
            'password' => 'password',
        ];

        $this->postJson(route('user.login', $request))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_login_without_email_and_password(): void
    {
        $this->postJson(route('user.login'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_cannot_login_if_email_does_not_exist_in_database(): void
    {
        $request = [
            'email' => 'user@test.com',
            'password' => 'password',
        ];

        $response = $this->postJson(route('user.login', $request))
            ->assertJsonMissingValidationErrors(['email', 'password'])
            ->assertUnauthorized()
            ->json();

        $this->assertEquals(UserMessageHelper::INVALID_CREDENTIALS, $response['message']);
        $this->assertCount(1, $response);
    }

    public function test_user_cannot_login_if_password_does_not_match_records(): void
    {
        $user = UserTestHelper::create(['email' => 'user@test.com']);
        $request = [
            'email' => $user->email,
            'password' => 'no_password_match',
        ];

        $response = $this->postJson(route('user.login', $request))
            ->assertJsonMissingValidationErrors(['email', 'password'])
            ->assertUnauthorized()
            ->json();

        $this->assertEquals(UserMessageHelper::INVALID_CREDENTIALS, $response['message']);
        $this->assertCount(1, $response);
    }

    public function test_user_can_login_when_email_and_password_match_records(): void
    {
        $accessTokenKey = LoginController::ACCESS_TOKEN_KEY;
        $user = UserTestHelper::create();
        $request = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->postJson(route('user.login', $request))->assertok();

        $this->assertArrayHasKey($accessTokenKey, $response->json());
        $this->assertNotNull($response->json()[$accessTokenKey]);
    }
}
