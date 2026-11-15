<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook_entries', function (Blueprint $t) {
            $t->id();
            $t->date('entry_date')->index();
            $t->string('type', 10)->index();               // purchase | expense
            $t->string('category', 60)->index();
            $t->string('description', 200);
            $t->string('payee', 120)->nullable();           // supplier or who was paid
            $t->decimal('amount', 12, 2);
            $t->string('method', 10)->default('cash');      // cash | momo | bank
            $t->string('reference', 60)->nullable();        // receipt / invoice number
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook_entries');
    }
};
