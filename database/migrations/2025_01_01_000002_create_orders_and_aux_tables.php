<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_number')->unique();
            $t->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $t->decimal('subtotal', 12, 2);
            $t->decimal('shipping', 12, 2)->default(0);
            $t->decimal('tax', 12, 2)->default(0);
            $t->decimal('total', 12, 2);
            $t->enum('status',['pending','processing','shipped','delivered','cancelled'])->default('pending');
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->index(['customer_id','status']);
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->integer('quantity');
            $t->decimal('unit_price', 12, 2);
            $t->decimal('line_total', 12, 2);
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->string('filename');
            $t->timestamps();
        });

        Schema::create('product_imports', function (Blueprint $t) {
            $t->id();
            $t->string('filename');
            $t->string('status')->default('queued');
            $t->json('result')->nullable();
            $t->timestamps();
        });

        Schema::create('refresh_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('token')->unique();
            $t->timestamp('expires_at');
            $t->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('product_imports');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
