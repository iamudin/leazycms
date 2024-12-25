<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('polling_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polling_option_id')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polling_responses');
    }
};
