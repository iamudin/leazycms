<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->char('shortcut',6)->unique()->default(null);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
