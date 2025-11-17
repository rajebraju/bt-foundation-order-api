<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'invoice_path')) {
            Schema::table('orders', function (Blueprint $t) {
                $t->string('invoice_path')->nullable()->after('meta');
            });
        }

        if (! Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $t) {
                $t->id();
                $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
                $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $t->integer('quantity')->default(0);
                $t->integer('change')->nullable();
                $t->string('reason')->nullable();
                $t->timestamps();
            });
        } else {
            Schema::table('inventories', function (Blueprint $t) {
                if (! Schema::hasColumn('inventories', 'order_id')) {
                    $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                }
                if (! Schema::hasColumn('inventories', 'change')) {
                    $t->integer('change')->nullable();
                }
                if (! Schema::hasColumn('inventories', 'reason')) {
                    $t->string('reason')->nullable();
                }
                if (! Schema::hasColumn('inventories', 'quantity')) {
                    $t->integer('quantity')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'invoice_path')) {
            Schema::table('orders', function (Blueprint $t) {
                $t->dropColumn('invoice_path');
            });
        }
    }
};
