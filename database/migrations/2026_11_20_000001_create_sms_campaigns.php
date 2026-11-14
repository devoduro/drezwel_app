<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('audience', 30);
            $t->string('audience_label', 120);
            $t->text('message');
            $t->unsignedInteger('recipients')->default(0);
            $t->unsignedInteger('sent')->default(0);
            $t->unsignedInteger('failed')->default(0);
            $t->string('status', 12)->default('sending'); // sending | done | aborted
            $t->string('note')->nullable();
            $t->timestamps();
        });

        Schema::table('sms_logs', function (Blueprint $t) {
            $t->foreignId('campaign_id')->nullable()->after('delivery_id')->constrained('sms_campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', fn (Blueprint $t) => $t->dropConstrainedForeignId('campaign_id'));
        Schema::dropIfExists('sms_campaigns');
    }
};
