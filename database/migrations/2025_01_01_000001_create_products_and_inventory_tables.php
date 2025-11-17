<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('sku')->unique();
            $t->string('name');
            $t->text('description')->nullable();
            $t->decimal('base_price', 12, 2);
            $t->boolean('is_active')->default(true);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['name']);
        });

        Schema::table('products', function (Blueprint $t) {
            $t->fullText(['name', 'description'], 'fulltext_name_description');
        });

        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->string('sku')->unique();
            $t->string('title')->nullable();
            $t->decimal('price', 12, 2);
            $t->integer('stock')->default(0);
            $t->json('attributes')->nullable();
            $t->timestamps();
            $t->index(['sku']);
        });

        Schema::create('inventories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->integer('quantity')->default(0);
            $t->timestamps();
            $t->unique(['variant_id']);
        });
    }
    public function down()
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
