<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_products_via_csv()
    {
        $this->seed();

        Storage::fake('local');
        $csvContent = "sku,name,price\nP001,Product A,100.00\nP002,Product B,200.00";
        Storage::put('imports/test.csv', $csvContent);

        $admin = \App\Models\User::whereEmail('admin@email.com')->first();
        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/products/import', [
                'file' => UploadedFile::fake()->createWithContent('products.csv', $csvContent),
            ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Import queued successfully']);

        // Check job was dispatched
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessProductImportJob::class);
    }
}