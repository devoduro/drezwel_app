<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('sku', 40)->nullable()->unique();
            $t->string('category', 60)->nullable()->index();
            $t->decimal('cost_price', 10, 2)->default(0);
            $t->decimal('selling_price', 10, 2)->default(0);
            $t->integer('stock_qty')->default(0);          // kept in step with stock_movements
            $t->unsignedInteger('reorder_level')->default(5); // alert when stock falls to this or below
            $t->timestamp('low_alerted_at')->nullable();      // so the manager is texted once per dip, not on every sale
            $t->boolean('is_active')->default(true);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->string('type', 20); // opening | purchase | sale | return | adjustment | damage
            $t->integer('qty');     // signed: + in, - out
            $t->integer('balance_after');
            $t->decimal('unit_cost', 10, 2)->nullable();
            $t->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('note')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['product_id', 'created_at']);
        });

        Schema::create('delivery_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->unsignedSmallInteger('qty');
            $t->decimal('unit_price', 10, 2);
            $t->decimal('unit_cost', 10, 2)->default(0); // cost at the time of sale, so profit stays right if prices change later
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
    }
};
