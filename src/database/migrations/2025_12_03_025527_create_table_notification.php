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
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('user_id')->index()->nullable();
                $table->string('title');
                $table->text('message');
                $table->string('type')->default('default');
                $table->string('url');
                $table->boolean('is_read')->default(false);
                $table->string('notificationable_type')->nullable();
                $table->string('notificationable_id')->index()->nullable();
                $table->timestamps();
            });



    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');

    }
};
