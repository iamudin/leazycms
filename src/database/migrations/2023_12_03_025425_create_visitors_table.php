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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id()->index();
            $table->foreignId('user_id')->index()->nullable();
            $table->foreignId('post_id')->index()->nullable();
            $table->ipAddress('ip')->nullable();
            $table->json('ip_location')->nullable();
            $table->string('browser')->nullable();
            $table->string( 'session')->index();
            $table->string( 'device')->nullable();
            $table->string( 'os')->nullable();
            $table->string( 'page')->index()->nullable();
            $table->string( 'reference')->nullable();
            $table->dateTime('created_at')->useCurrent();
            
        });
        Schema::table('visitors', function (Blueprint $table) {
            $table->index(['created_at']);
        });
     
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
