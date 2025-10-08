<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('domain')->nullable()->default(null)->index('idx_domain');
            $table->string('country')->nullable()->default(null)->index('idx_country');
            $table->string('city')->nullable()->default(null)->index('idx_city');
            $table->string('region')->nullable()->default(null)->index('idx_region');
            $table->string('country_code')->nullable()->default(null)->index('idx_country_code');
            $table->timestamp('last_activity')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->string('user_agent', 255)->nullable()->index();
        });
  }
 public function down(): void
    {

    }

};