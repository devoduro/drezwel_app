<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            // The "was" price. When it is higher than the selling price the store shows it struck through with a -% badge.
            $t->decimal('compare_at_price', 10, 2)->nullable()->after('selling_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('compare_at_price'));
    }
};
