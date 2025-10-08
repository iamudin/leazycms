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
        Schema::table('visitors', function (Blueprint $table) 
        {
            if (!Schema::hasColumn('visitors', 'domain')) {
                $table->string('domain')->default(null)->index('idx_domain');
            }
            if (!Schema::hasColumn('visitors', 'country')) {
                $table->string('country')->default(null)->index('idx_country');
            }
            if (!Schema::hasColumn('visitors', 'city')) {
                $table->string('city')->default(null)->index('idx_city');
            }
            if (!Schema::hasColumn('visitors', 'region')) {
                $table->string('region')->default(null)->index('idx_region');
            }
            if (!Schema::hasColumn('visitors', 'country_code')) {
                $table->string('country_code')->default(null)->index('idx_country_code');
            }
            if (!Schema::hasColumn('visitors', 'last_activity')) {
                $table->timestamp('last_activity')->useCurrent()->useCurrentOnUpdate();
            }
             if (!Schema::hasColumn('visitors', 'user_agent')) {
                $table->string('user_agent', 255)->index();
            }

            // Hapus kolom hanya jika ada
            if (Schema::hasColumn('visitors', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('visitors', 'reference')) {
                $table->dropColumn('reference');
            }
            if (Schema::hasColumn('visitors', 'ip_location')) {
                $table->dropColumn('ip_location');
            }
            if (Schema::hasColumn('visitors', 'times')) {
                $table->dropColumn('times');
            }
              if (Schema::hasColumn('visitors', 'times')) {
                $table->dropColumn('times');
            }
            if (Schema::hasColumn('visitors', 'page')) {
                $table->dropColumn('page');
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
