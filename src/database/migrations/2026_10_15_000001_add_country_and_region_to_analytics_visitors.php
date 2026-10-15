<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('analytics_visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_visitors', 'country')) {
                $table->string('country', 120)->nullable()->index();
            }
            if (!Schema::hasColumn('analytics_visitors', 'region')) {
                $table->string('region', 120)->nullable();
            }
            if (!Schema::hasColumn('analytics_visitors', 'city')) {
                $table->string('city', 120)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('analytics_visitors', function (Blueprint $table) {
            if (Schema::hasColumn('analytics_visitors', 'country')) {
                $table->dropIndex(['country']);
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('analytics_visitors', 'region')) {
                $table->dropColumn('region');
            }
            if (Schema::hasColumn('analytics_visitors', 'city')) {
                $table->dropColumn('city');
            }
        });
    }
};
