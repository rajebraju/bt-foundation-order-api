<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\GenerateInvoicePdfJob;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_confirm_order_generates_invoice_job()
    {
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\ProductSeeder::class);

        // use seeded emails from UserSeeder
        $customer = User::where('email', 'customer@email.com')->first();

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        /** @var string $token */
        $token = $auth->login($customer);

        $resp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/orders', [
                'items' => [['variant_id' => ProductVariant::first()->id, 'quantity' => 1]],
                'shipping' => 0,
                'tax' => 0
            ]);

        $resp->assertStatus(201);

        $orderId = $resp->json('id');

        Queue::fake();

        $admin = User::where('email', 'admin@email.com')->first();

        /** @var string $adminToken */
        $adminToken = $auth->login($admin);

        $confirm = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/orders/{$orderId}/confirm");

        $confirm->assertStatus(200);

        Queue::assertPushed(GenerateInvoicePdfJob::class);
    }
}
