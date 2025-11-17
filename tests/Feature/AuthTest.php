<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_tokens()
    {
        $user = User::factory()->create(['email' => 'admin@email.com', 'password' => bcrypt('password')]);
        
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@email.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'refresh_token']);
    }

    public function test_refresh_token_works()
    {
        $user = User::factory()->create();
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $refreshToken = $login->json('refresh_token');
        $refresh = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $refresh->assertStatus(200)
                ->assertJsonStructure(['access_token', 'refresh_token']);
    }

    public function test_logout_invalidates_token()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);

        // Try using same token again
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/products')
            ->assertStatus(401);
    }
}