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
        if (!Schema::hasColumn('themes', 'category')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->string('category')->nullable()->after('name');
            });
        }
          if (!Schema::hasColumn('themes', 'description')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('themes', 'category')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
          if (Schema::hasColumn('themes', 'description')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
