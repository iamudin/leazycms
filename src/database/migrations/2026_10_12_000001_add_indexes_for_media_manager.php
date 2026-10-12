<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Index kolom media pada posts (untuk lookup cepat media di tabel posts)
        try {
            DB::statement('CREATE INDEX posts_media_index ON posts (media(191))');
        } catch (\Exception $e) {
            // Abaikan jika index sudah ada
        }

        // 2. Fulltext index pada kolom content (untuk pencarian instan referensi media pada konten artikel)
        try {
            DB::statement('CREATE FULLTEXT INDEX posts_content_fulltext ON posts (content)');
        } catch (\Exception $e) {
            // Abaikan jika index sudah ada
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('DROP INDEX posts_media_index ON posts');
        } catch (\Exception $e) {
        }

        try {
            DB::statement('DROP INDEX posts_content_fulltext ON posts');
        } catch (\Exception $e) {
        }
    }
};
