<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::create('online_users', function (Blueprint $table) {
            $table->string('session_id')->primary();
            $table->string('domain');
            $table->timestamp('last_activity');
            $table->string('ip')->nullable();
            $table->index(['domain', 'last_activity']);
        });
  }
 public function down(): void
    {

    }

};