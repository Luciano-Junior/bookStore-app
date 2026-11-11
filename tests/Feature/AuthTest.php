<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * User can login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@bookstore.dev',
            'password' => bcrypt('BookStore@2026'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@bookstore.dev',
            'password' => 'BookStore@2026',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    /**
     * Login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@bookstore.dev',
            'password' => bcrypt('BookStore@2026'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@bookstore.dev',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    /**
     * Login validation returns 422 when fields are missing.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password',
            ]);
    }

    /**
     * Authenticated user can retrieve their profile.
     */
    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response
            ->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    /**
     * User cannot access protected endpoints without authentication.
     */
    public function test_guest_cannot_access_user_endpoint(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    /**
     * User can logout and revoke the current token.
     */
    public function test_user_can_logout_and_revoke_token(): void
    {
         $user = User::factory()->create();

        $token = $user->createToken('test-token');

        // Realiza logout
        $this->withToken($token->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk();

        // Confirma que o token foi removido do banco
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);

        // Limpa os guards de autenticação em memória
        $this->app['auth']->forgetGuards();

        // Tenta acessar novamente utilizando o mesmo token
        $this->withToken($token->plainTextToken)
            ->getJson('/api/user')
            ->assertUnauthorized();
        }
}
