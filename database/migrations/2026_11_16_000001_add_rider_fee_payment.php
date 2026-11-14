<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $t) {
            // The customer pays product price + delivery fee to the rider, the rider hands it all in,
            // and the business pays the rider's delivery fee back. These record that last step.
            $t->timestamp('rider_fee_paid_at')->nullable()->after('rider_fee_status');
            $t->foreignId('rider_fee_paid_by')->nullable()->after('rider_fee_paid_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $t) {
            $t->dropConstrainedForeignId('rider_fee_paid_by');
            $t->dropColumn('rider_fee_paid_at');
        });
    }
};
