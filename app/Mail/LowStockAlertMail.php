<?php
namespace App\Mail;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;
    public ProductVariant $variant;
    public function __construct(ProductVariant $variant){ $this->variant = $variant; }
    public function build()
    {
        return $this->subject("Low stock alert: {$this->variant->sku}")
                    ->view('emails.low_stock')
                    ->with(['variant' => $this->variant]);
    }
}
