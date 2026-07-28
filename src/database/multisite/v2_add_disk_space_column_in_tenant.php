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
        if (!Schema::hasColumn('tenants', 'disk_space')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->integer('disk_space')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tenants', 'disk_space')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('disk_space');
            });
        }
    }
};
