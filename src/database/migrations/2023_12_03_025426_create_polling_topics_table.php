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
        Schema::create('polling_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('keyword')->index()->unique();
            $table->text('description')->nullable();
            $table->enum('status',['publish','draft'])->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polling_topics');
    }
};
