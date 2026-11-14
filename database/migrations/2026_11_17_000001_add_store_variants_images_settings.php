<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key', 80)->primary();
            $t->text('value')->nullable();
        });

        Schema::table('products', function (Blueprint $t) {
            $t->string('slug', 190)->nullable()->unique()->after('name');
            $t->text('description')->nullable()->after('category');
            $t->boolean('show_in_store')->default(true)->after('is_active');
        });

        // Stock now lives on variants (size / colour). A product with no sizes or colours has one plain variant.
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('size', 40)->nullable();
            $t->string('color', 40)->nullable();
            $t->string('sku', 40)->nullable();
            $t->decimal('price', 10, 2)->nullable(); // overrides the product's selling price when set
            $t->integer('stock_qty')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['product_id', 'is_active']);
        });

        Schema::create('product_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::table('stock_movements', function (Blueprint $t) {
            $t->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
        });
        Schema::table('delivery_items', function (Blueprint $t) {
            $t->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
        });
        Schema::table('deliveries', function (Blueprint $t) {
            $t->string('source', 12)->default('manager')->after('order_type')->index(); // manager | online | pos
        });

        // Move every existing product onto a default variant that carries its stock.
        foreach (DB::table('products')->get() as $p) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $p->id, 'stock_qty' => $p->stock_qty, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('stock_movements')->where('product_id', $p->id)->update(['variant_id' => $variantId]);
            DB::table('delivery_items')->where('product_id', $p->id)->update(['variant_id' => $variantId]);
            DB::table('products')->where('id', $p->id)->update(['slug' => Str::slug($p->name) . '-' . $p->id]);
        }
    }

    public function down(): void
    {
        Schema::table('deliveries', fn (Blueprint $t) => $t->dropColumn('source'));
        Schema::table('delivery_items', fn (Blueprint $t) => $t->dropConstrainedForeignId('variant_id'));
        Schema::table('stock_movements', fn (Blueprint $t) => $t->dropConstrainedForeignId('variant_id'));
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variants');
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['slug', 'description', 'show_in_store']));
        Schema::dropIfExists('settings');
    }
};
