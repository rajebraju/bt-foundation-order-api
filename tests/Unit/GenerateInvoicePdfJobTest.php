<?php

namespace Tests\Unit;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateInvoicePdfJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_pdf_is_generated_and_saved()
    {
        Storage::fake('local');

        $order = Order::factory()->create(['total' => 150.00]);
        $job = new GenerateInvoicePdfJob($order);

        $job->handle();

        $filePath = "invoices/{$order->order_number}.pdf";
        Storage::assertExists($filePath);

        $order->refresh();
        $this->assertNotNull($order->invoice_path);
        $this->assertEquals($filePath, $order->invoice_path);
    }
}
