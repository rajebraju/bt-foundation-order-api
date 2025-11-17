<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use App\Models\ProductVariant;
use Mail;

class SendLowStockNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array|Collection */
    public $variants;

    /**
     * Create a new job instance.
     *
     * @param  array|Collection  $variants
     * @return void
     */
    public function __construct($variants)
    {
        // Accept either a collection or array of ProductVariant instances
        $this->variants = $variants instanceof Collection ? $variants->all() : (array) $variants;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // send an email or notification for low-stock variants
        // keep logic simple for tests — just iterate variants
        foreach ($this->variants as $variant) {
            // In production you'd resolve vendor/email and queue mail notifications.
            // Here we keep no-op or log for tests.
            // Example (commented) Mail::to($variant->product->vendor->email)->send(new LowStockMail($variant));
        }
    }
}
