<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\ProductVariant;
use App\Jobs\GenerateInvoicePdfJob;
use Illuminate\Support\Facades\Queue;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_confirm_order_generates_invoice_job()
    {
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\ProductSeeder::class);

        $customer = User::where('email','customer@example.com')->first();

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        /** @var string $token */
        $token = $auth->login($customer);

        $resp = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/orders', [
                'items'=>[['variant_id'=>ProductVariant::first()->id,'quantity'=>1]],
                'shipping'=>0,
                'tax'=>0
            ]);

        $resp->assertStatus(201);

        $orderId = $resp->json('id');

        // fake queue must be set BEFORE request
        Queue::fake();

        $admin = User::where('email','admin@example.com')->first();

        /** @var string $adminToken */
        $adminToken = $auth->login($admin);

        $confirm = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/orders/{$orderId}/confirm");

        $confirm->assertStatus(200);

        Queue::assertPushed(GenerateInvoicePdfJob::class);
    }
}
