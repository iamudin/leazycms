<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('domain')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('region')->nullable()->change();
            $table->string('country_code')->nullable()->change();
            $table->timestamp('last_activity')->nullable()->change();
            $table->string('user_agent', 255)->nullable()->change();
        });
  }
 public function down(): void
    {

    }

};