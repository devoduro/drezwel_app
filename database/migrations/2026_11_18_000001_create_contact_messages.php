<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('type', 12)->default('contact')->index(); // contact | wholesale
            $t->string('name', 120);
            $t->string('phone', 20);
            $t->string('email', 120)->nullable();
            $t->string('subject', 160)->nullable();
            $t->text('message');
            $t->string('status', 10)->default('new')->index();   // new | handled
            $t->string('ip', 45)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
