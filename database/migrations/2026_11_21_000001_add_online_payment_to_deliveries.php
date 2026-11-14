<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $t) {
            $t->string('payment_method', 10)->nullable()->after('source');                       // cod | speso
            $t->string('payment_status', 12)->nullable()->after('payment_method')->index();      // awaiting | paid | failed | abandoned
            $t->string('payment_order_id', 60)->nullable()->unique()->after('payment_status');   // order_id sent to Speso (reference-attempt)
            $t->string('payment_reference', 80)->nullable()->after('payment_order_id');          // Speso's own reference
            $t->string('payment_network', 4)->nullable()->after('payment_reference');
            $t->string('payment_mobile', 15)->nullable()->after('payment_network');
            $t->unsignedTinyInteger('payment_attempts')->default(0)->after('payment_mobile');
            $t->timestamp('payment_requested_at')->nullable()->after('payment_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $t) {
            $t->dropUnique(['payment_order_id']);
            $t->dropIndex(['payment_status']);
            $t->dropColumn(['payment_method', 'payment_status', 'payment_order_id', 'payment_reference', 'payment_network', 'payment_mobile', 'payment_attempts', 'payment_requested_at']);
        });
    }
};
