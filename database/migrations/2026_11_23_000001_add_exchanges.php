<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An exchange / sale return is an ordinary order whose returned items are lines with a NEGATIVE quantity:
        // every total (sales, profit, units sold, stock ledger) then nets out without special cases.
        Schema::table('delivery_items', function (Blueprint $t) {
            $t->smallInteger('qty')->change();
        });

        Schema::table('deliveries', function (Blueprint $t) {
            $t->foreignId('exchange_of_id')->nullable()->after('source')->constrained('deliveries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $t) {
            $t->dropConstrainedForeignId('exchange_of_id');
        });
        Schema::table('delivery_items', function (Blueprint $t) {
            $t->unsignedSmallInteger('qty')->change();
        });
    }
};
