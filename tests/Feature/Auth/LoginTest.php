<?php

//declare(strict_types=1);
//
//namespace Tests\Feature\Auth;
//
//use App\Helpers\UserMessageHelper;
//use Laravel\Sanctum\PersonalAccessToken;
//use Laravel\Sanctum\Sanctum;
//use Tests\Helpers\UserTestHelper;
//use Tests\TestCase;
//
//class LoginTest extends TestCase
//{
//    public function test_user_cannot_login_without_valid_email(): void
//    {
//        Sanctum::actingAs(UserTestHelper::create());
//        $request = [
//            'email' => 'invalid_email',
//            'password' => 'password',
//        ];
//
//        $this->postJson(route('login', $request))
//            ->assertUnprocessable()
//            ->assertJsonValidationErrors(['email']);
//    }
//
//    public function test_user_cannot_login_without_email_and_password(): void
//    {
//        Sanctum::actingAs(UserTestHelper::create());
//        $this->postJson(route('login'))
//            ->assertUnprocessable()
//            ->assertJsonValidationErrors(['email', 'password']);
//    }
//
//    public function test_user_cannot_login_if_email_does_not_exist_in_database(): void
//    {
//        Sanctum::actingAs(UserTestHelper::create());
//        $request = [
//            'email' => 'user@test.com',
//            'password' => 'password',
//        ];
//
//        $response = $this
//            ->postJson(route('login', $request))
//            ->assertJsonMissingValidationErrors(['email', 'password'])
//            ->assertOk()
//            ->json('data');
//
//        $this->assertEquals(UserMessageHelper::INVALID_CREDENTIALS, $response['message']);
//    }
//
//    public function test_user_cannot_login_if_password_does_not_match_records(): void
//    {
//        $user = Sanctum::actingAs(UserTestHelper::create());
//        $request = [
//            'email' => $user->email,
//            'password' => 'no_password_match',
//        ];
//
//        $response = $this->postJson(route('login', $request))
//            ->assertJsonMissingValidationErrors(['email', 'password'])
//            ->assertOk()
//            ->json('data');
//
//        $this->assertEquals(UserMessageHelper::INVALID_CREDENTIALS, $response['message']);
//    }
//
//    public function test_user_can_login_when_email_and_password_match_records(): void
//    {
//        $user = Sanctum::actingAs(UserTestHelper::create());
//        $request = [
//            'email' => $user->email,
//            'password' => 'password',
//        ];
//
//        $response = $this->postJson(route('login', $request))->assertok()->json('data');
//
//        $this->assertAuthenticatedAs($user);
//        $this->assertNotNull($response['accessToken']);
//        $this->assertDatabaseCount(PersonalAccessToken::class, 1);
//        $this->assertDatabaseHas(PersonalAccessToken::class, ['tokenable_id' => $user->id]);
//    }
//}
