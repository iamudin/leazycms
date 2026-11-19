<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index tambahan untuk query yang paling sering dijalankan di halaman publik & panel:
 * - posts: where type = ? and status = 'publish' order by created_at desc (listing modul, arsip, sitemap)
 * - posts: deleted_at (SoftDeletes menambahkan "deleted_at is null" pada setiap query)
 * - categories: where type = ? and status = 'publish'
 * - polling_options.polling_topic_id & polling_responses.polling_option_id (relasi polling)
 *
 * Aman dijalankan berulang: setiap index dicek terlebih dahulu.
 */
return new class extends Migration
{
    private array $indexes = [
        'posts' => [
            'posts_type_status_created_at_index' => ['type', 'status', 'created_at'],
            'posts_deleted_at_index' => ['deleted_at'],
        ],
        'categories' => [
            'categories_type_status_index' => ['type', 'status'],
        ],
        'polling_options' => [
            'polling_options_polling_topic_id_index' => ['polling_topic_id'],
        ],
        'polling_responses' => [
            'polling_responses_polling_option_id_index' => ['polling_option_id'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes as $name => $columns) {
                if (!Schema::hasColumns($table, $columns) || Schema::hasIndex($table, $name) || Schema::hasIndex($table, $columns)) {
                    continue;
                }
                try {
                    Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                        $blueprint->index($columns, $name);
                    });
                } catch (\Throwable $e) {
                    // Abaikan jika index setara sudah ada dengan nama lain.
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach (array_keys($indexes) as $name) {
                if (!Schema::hasIndex($table, $name)) {
                    continue;
                }
                try {
                    Schema::table($table, function (Blueprint $blueprint) use ($name) {
                        $blueprint->dropIndex($name);
                    });
                } catch (\Throwable $e) {
                }
            }
        }
    }
};
