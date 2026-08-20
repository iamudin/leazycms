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
        if (!Schema::hasColumn('themes', 'demo_url')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->string('demo_url')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     if (Schema::hasColumn('themes', 'demo_url')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->dropColumn('demo_url');
            });
        }
    }
};
