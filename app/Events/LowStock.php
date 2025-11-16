<?php
namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\ProductVariant;

class LowStock
{
    use SerializesModels;
    public ProductVariant $variant;
    public function __construct(ProductVariant $variant)
    {
        $this->variant = $variant;
    }
}
