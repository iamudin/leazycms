<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
             if (!Schema::hasColumn('tenants', 'plugins')) {
            $table->json('plugins')->nullable();
             }
        });
    }
    public function down(): void
    {
             if (Schema::hasColumn('tenants', 'plugins')) {

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('plugins');
        });
    }
    }

};