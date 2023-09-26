<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

// register
    public function testUserRegistration()
    {
        $userData = [
            'username' => 'John Doe',
            'password' => Hash::make('password123'),
            'birthday' => '2012-12-31',
        ];

        $response = $this->json('POST', '/api/auth/register', $userData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'username' => 'John Doe',
            'birthday' => '2012-12-31',
        ]);
    }

    public function testUserRegistrationWithDuplicateUsername()
    {
        $userData = [
            'username' => 'John Doe',
            'password' => 'password123',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/register', [
            'username' => 'John Doe',
            'password' => 'password123',
            'birthday' => '2012-12-31',
        ]);

        $response->assertStatus(403);

        $response->assertJson([
            'error' => 'User with this username already exists.',
        ]);
    }

// login
    public function testUserLogin()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['access_token']);
    
        $this->assertAuthenticated('api'); // 断言已经通过认证
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'error' => 'Unauthorized',
        ]);

        $this->assertGuest('api');
    }

// logout
    public function testUserLogout()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('POST', '/api/auth/logout');

        $response->assertStatus(200);

        $this->assertGuest('api');
    }

    public function testUserLogoutFail()
    {
        $response = $this->json('POST', '/api/auth/logout', [
            'username' => 'nonexistentuser',
            'password' => 'invalidpassword',
        ]);

        $response->assertStatus(401);

        $this->assertGuest('api');
    }

    public function testUserSearchAll()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('GET', '/api/user/');

        $response->assertStatus(200);
    }

    public function testUserSearchByUserName()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('GET', '/api/user/admin');

        $response->assertStatus(200);
    }

    public function testUserSearchByUserNameFail()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('GET', '/api/user/admin1');

        $response->assertStatus(401);
    }

    public function testUserUpdate()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('PUT', '/api/user/admin',[
            'username' => 'admin',
            'password' => 'nimda',
            'birthday' => '2023-01-01',
        ]);

        $response->assertStatus(200);
    }

    public function testUserUpdateFail()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('PUT', '/api/user/admin1',[
            'username' => 'admin',
            'password' => 'nimda',
            'birthday' => '2023-01-01',
        ]);

        $response->assertStatus(404);
    }

    public function testUserDelete()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('DELETE', '/api/user/admin');

        $response->assertStatus(200);
    }

    public function testUserDeleteFailUserName()
    {
        $userData = [
            'username' => 'admin',
            'password' => 'admin',
            'birthday' => '2012-12-31',
        ];

        $this->json('POST', '/api/auth/register', $userData);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $token = $response->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->json('DELETE', '/api/user/admin1');

        $response->assertStatus(404);
    }
}
