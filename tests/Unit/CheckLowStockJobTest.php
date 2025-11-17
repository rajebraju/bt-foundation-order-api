<?php

namespace Tests\Unit;

use App\Jobs\CheckLowStockJob;
use App\Jobs\SendLowStockNotificationJob;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckLowStockJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_finds_low_stock_variants()
    {
        Queue::fake();

        ProductVariant::factory()->create(['stock' => 2, 'sku' => 'LOW-001']); // Below threshold
        ProductVariant::factory()->create(['stock' => 15, 'sku' => 'OK-001']); // Above threshold

        (new CheckLowStockJob())->handle();

        Queue::assertPushed(SendLowStockNotificationJob::class, function ($job) {
            return count($job->variants) === 1 && $job->variants[0]->sku === 'LOW-001';
        });
    }
}
