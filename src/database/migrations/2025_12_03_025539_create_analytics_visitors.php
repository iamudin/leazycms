<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::create('analytics_visitors', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 150);
            $table->string('visitor_key', 64);
            $table->string('session_id', 120)->nullable();
            $table->string('current_page', 191)->nullable();
            $table->string('device', 20)->nullable();
            $table->string('ip', 100)->nullable();
            $table->string('referrer', 191)->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->date('last_seen_date')->nullable();
            $table->timestamps();

            $table->unique(['domain', 'visitor_key']);

            $table->index('last_seen_at');
            $table->index('last_seen_date');
            $table->index('device');
            $table->index('ip');
            $table->index('domain');
        });
  }
 public function down(): void
    {

    }

};