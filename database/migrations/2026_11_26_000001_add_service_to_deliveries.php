<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $t) {
            $t->string('service', 10)->nullable()->after('delivery_mode');   // door | express | pickup (website orders)
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $t) {
            $t->dropColumn('service');
        });
    }
};
