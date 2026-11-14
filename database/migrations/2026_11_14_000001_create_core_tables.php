<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable()->unique();
            $t->string('phone', 20)->nullable()->unique();
            $t->string('password');
            $t->string('role', 20)->default('rider')->index(); // manager | rider
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->mediumText('value');
            $t->integer('expiration');
        });
        Schema::create('cache_locks', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->string('owner');
            $t->integer('expiration');
        });

        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone', 20)->unique();
            $t->string('location')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $t) {
            $t->id();
            $t->string('reference', 30)->unique();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->string('location');
            $t->string('package_description');
            $t->string('delivery_mode', 20)->default('rider');   // rider | station_bus | parcel_office | pick_up
            $t->string('order_type', 20)->default('standard');   // standard | selection_trial | exchange
            $t->text('notes')->nullable();
            $t->date('delivery_date');
            $t->string('time_window', 20)->nullable();
            $t->decimal('total_amount', 10, 2)->default(0);
            $t->decimal('amount_paid', 10, 2)->default(0);
            $t->decimal('delivery_fee', 10, 2)->default(0);
            $t->string('rider_fee_status', 10)->default('pending'); // pending | paid
            $t->unsignedSmallInteger('items_sent')->default(1);
            $t->unsignedSmallInteger('items_kept')->nullable();
            $t->string('status', 20)->default('pending')->index();   // pending|assigned|out_for_delivery|delivered|undelivered|cancelled
            $t->foreignId('rider_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('assigned_at')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->string('undelivered_reason')->nullable();
            $t->string('code_hash')->nullable();
            $t->unsignedTinyInteger('code_attempts')->default(0);
            $t->timestamp('code_sent_at')->nullable();
            $t->boolean('confirmed_by_manager')->default(false);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['delivery_date', 'status']);
            $t->index(['rider_id', 'status']);
        });

        Schema::create('delivery_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 30);
            $t->string('note')->nullable();
            $t->timestamp('created_at')->useCurrent();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rider_id')->nullable()->constrained('users')->nullOnDelete(); // set when a rider collected the cash
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->decimal('amount', 10, 2);
            $t->string('method', 20)->default('cash'); // cash | momo | bank
            $t->string('notes')->nullable();
            $t->timestamp('paid_at')->useCurrent();
            $t->timestamps();
        });

        Schema::create('remittances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rider_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->decimal('cash', 10, 2)->default(0);
            $t->unsignedSmallInteger('items')->default(0);
            $t->string('method', 30)->default('cash');
            $t->string('notes')->nullable();
            $t->timestamp('remitted_at')->useCurrent();
            $t->timestamps();
        });

        Schema::create('sms_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();
            $t->string('phone', 20);
            $t->string('purpose', 30);
            $t->text('message');
            $t->boolean('success')->default(false);
            $t->string('response')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['sms_logs', 'remittances', 'payments', 'delivery_events', 'deliveries', 'customers', 'cache_locks', 'cache', 'sessions', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
