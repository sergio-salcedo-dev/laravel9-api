<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Helpers\UserMessageHelper;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\Helpers\UserTestHelper;
use Tests\TestCase;

use function route;

class UserLogoutTest extends TestCase
{
    public function test_user_cannot_logout_if_not_logged_in(): void
    {
        $this->postJson(route('user.logout'))->assertUnauthorized();
    }

    public function test_user_can_logout_if_logged_in(): void
    {
        Sanctum::actingAs(UserTestHelper::create());
        $response = $this->postJson(route('user.logout'))->assertOk();

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(UserMessageHelper::LOGGED_OUT, $response->json('message'));
    }

    public function test_user_tokens_are_removed_when_user_logged_out(): void
    {
        $user = UserTestHelper::create();
        $request = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $this->postJson(route('user.login', $request))->assertok()->json();

        $response = $this->postJson(route('user.logout'))->assertOk();

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(UserMessageHelper::LOGGED_OUT, $response->json('message'));
        $this->assertDatabaseCount(PersonalAccessToken::class, 0);
    }
}
