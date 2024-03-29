<?php
//
//declare(strict_types=1);
//
//namespace Tests\Feature\Auth;
//
//use App\Models\User;
//use Laravel\Sanctum\Sanctum;
//use Tests\Helpers\UserTestHelper;
//use Tests\TestCase;
//
//class RegisterTest extends TestCase
//{
//    public function test_user_cannot_register_without_name_email_and_password(): void
//    {
//        $this->postJson(route('register'))
//            ->assertUnprocessable()
//            ->assertJsonValidationErrors(['name', 'email', 'password']);
//    }
//
//    public function test_user_cannot_register_without_confirming_password(): void
//    {
//        $request = [
//            'name' => 'user',
//            'email' => 'user@test.com',
//            'password' => 'password',
//        ];
//
//        $this->postJson(route('register', $request))
//            ->assertUnprocessable()
//            ->assertJsonValidationErrors(['password'])
//            ->assertJsonMissingValidationErrors(['name', 'email']);
//    }
//
//    public function test_user_cannot_register_without_a_valid_email(): void
//    {
//        $request = [
//            'name' => 'user',
//            'email' => 'invalid_email',
//            'password' => 'password',
//            'password_confirmation' => 'password',
//        ];
//
//        $this->postJson(route('register', $request))
//            ->assertUnprocessable()
//            ->assertJsonValidationErrors(['email'])
//            ->assertJsonMissingValidationErrors(['name', 'password']);
//    }
//
//    public function test_user_can_register_when_meets_validation_rules(): void
//    {
//        $request = [
//            'name' => 'user',
//            'email' => 'user@test.com',
//            'password' => 'password',
//            'password_confirmation' => 'password',
//        ];
//
//        $response = $this->postJson(route('register', $request))
//            ->assertCreated()
//            ->assertJsonMissingValidationErrors((['name', 'email', 'password']))
//            ->json('data');
//
//        $registeredUser = User::find($response['id'])->makeVisible('password');
//        $this->assertNotEquals('password', $registeredUser->password);
//        $this->assertDatabaseHas(User::class, ['email' => 'user@test.com']);
//    }
//
//    public function test_user_cannot_register_if_email_exists_in_database(): void
//    {
//        $request = [
//            'name' => 'user',
//            'email' => 'user@test.com',
//            'password' => 'password',
//            'password_confirmation' => 'password',
//        ];
//
//        UserTestHelper::create(['email' => 'user@test.com']);
//
//        $this->postJson(route('register', $request))
//            ->assertUnprocessable()
//            ->assertJsonMissingValidationErrors((['name', 'password']))
//            ->assertJsonValidationErrors(('email'));
//
//        $this->assertDatabaseCount(User::class, 1);
//    }
//}
