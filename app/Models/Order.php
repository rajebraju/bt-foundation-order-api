<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'customer_id',
        'subtotal',
        'shipping',
        'tax',
        'total',
        'status',
        'meta',
        'invoice_path',
    ];
    protected $casts = ['meta' => 'array'];

    public function user(): BelongsTo
    {
        return $this->customer();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
