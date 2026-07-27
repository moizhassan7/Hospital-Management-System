<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    public function test_show_change_password_form_returns_view()
    {
        $user = new User();
        $user->name = 'Test Staff';
        $user->username = 'teststaff';
        $user->user_scope = User::SCOPE_COLLECTION_CENTER;

        Auth::shouldReceive('user')->andReturn($user);

        $controller = new UserController();
        $response = $controller->showChangePasswordForm();

        $this->assertEquals('users.change-password', $response->name());
        $this->assertEquals($user, $response->getData()['user']);
    }

    public function test_update_password_logic_validation_and_hashing()
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->password = bcrypt('old-password-123');

        $user->shouldReceive('save')->once()->andReturn(true);

        Auth::shouldReceive('user')->andReturn($user);

        $controller = new UserController();
        $request = Request::create('/change-password', 'PUT', [
            'old_password' => 'old-password-123',
            'new_password' => 'new-secure-password',
            'new_password_confirmation' => 'new-secure-password',
        ]);

        $response = $controller->updatePassword($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
    }
}
