<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::table('analytics_visitors', function (Blueprint $table) {
            $table->string('user_agent')->nullable();
        });
  }
 public function down(): void
    {

    }

};