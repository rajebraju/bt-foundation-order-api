<?php
namespace App\Jobs;

use App\Models\ProductVariant;
use App\Mail\LowStockAlertMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendLowStockNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public ProductVariant $variant;

    public function __construct(ProductVariant $variant)
    {
        $this->variant = $variant->fresh('product.vendor');
    }

    public function handle()
    {
        // Notify vendor and admin (simplified)
        $emails = [];

        if ($this->variant->product && $this->variant->product->vendor) {
            $emails[] = $this->variant->product->vendor->email;
        }

        $admin = User::role('admin')->first();
        if ($admin) $emails[] = $admin->email;

        foreach (array_unique($emails) as $email) {
            Mail::to($email)->send(new LowStockAlertMail($this->variant));
        }
    }
}
