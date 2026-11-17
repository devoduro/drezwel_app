<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->boolean('is_deal')->default(false)->after('show_in_store')->index();   // chosen by the manager for "Today's deals"
        });

        // Until now a product was in Today's deals whenever its old price was above its selling price. Keep those where they are.
        DB::table('products')->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'selling_price')->update(['is_deal' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->dropIndex(['is_deal']);
            $t->dropColumn('is_deal');
        });
    }
};
