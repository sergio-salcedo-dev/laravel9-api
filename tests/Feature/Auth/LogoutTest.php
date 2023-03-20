<?php
//
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
//use function route;
//
//class LogoutTest extends TestCase
//{
//    public function test_user_cannot_logout_if_not_logged_in(): void
//    {
//        $this->postJson(route('logout'))->assertUnauthorized();
//    }
//
//    public function test_user_can_logout_if_logged_in(): void
//    {
//        Sanctum::actingAs(UserTestHelper::create());
//        $response = $this->postJson(route('logout'))->assertOk()->json('data');
//
//        $this->assertArrayHasKey('message', $response);
//        $this->assertEquals(UserMessageHelper::LOGGED_OUT, $response['message']);
//    }
//
//    public function test_user_tokens_are_removed_when_user_logged_out(): void
//    {
//        $user = Sanctum::actingAs(UserTestHelper::create());
//        $request = [
//            'email' => $user->email,
//            'password' => 'password',
//        ];
//
//        $this->postJson(route('login', $request))->assertok()->json();
//
//        $response = $this->postJson(route('logout'))->assertOk()->json('data');
//
//        $this->assertArrayHasKey('message', $response);
//        $this->assertEquals(UserMessageHelper::LOGGED_OUT, $response['message']);
//        $this->assertDatabaseCount(PersonalAccessToken::class, 0);
//    }
//}
