<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product()
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $admin = User::where('email','admin@example.com')->first();

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        /** @var string $token */
        $token = $auth->login($admin);

        $resp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/products', [
                'sku'=>'PRD-TEST-1',
                'name'=>'Test product',
                'base_price'=>100,
            ]);

        $resp->assertStatus(201);
        $this->assertDatabaseHas('products',['sku'=>'PRD-TEST-1']);
    }

    public function test_customer_cannot_create_product()
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $customer = User::where('email','customer@example.com')->first();

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        /** @var string $token */
        $token = $auth->login($customer);

        $resp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/products', [
                'sku'=>'PRD-TEST-2',
                'name'=>'Test product 2',
                'base_price'=>200,
            ]);

        $resp->assertStatus(403);
    }
}
