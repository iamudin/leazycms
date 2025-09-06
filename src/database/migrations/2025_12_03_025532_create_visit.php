<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
   Schema::table('visitors', function (Blueprint $table) {
    $table->string('page_hash', 32)->nullable()->index();
    $table->index(['ip','session','page_hash','updated_at'], 'visitor_lookup_idx');
});
    }
    public function down(): void
    {
    Schema::table('visitors', function (Blueprint $table) {
            // Rollback perubahan
            $table->dropIndex('visitor_lookup_idx');
            // $table->dropUnique('visitor_unique_idx'); // jika diaktifkan
            $table->dropColumn('page_hash');
        });
    }
};
