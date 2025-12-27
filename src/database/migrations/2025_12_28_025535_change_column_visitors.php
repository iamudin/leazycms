<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::table('visitor_logs', function (Blueprint $table) {

            // Ubah panjang kolom
            $table->string('page', 1000)->change();
            $table->string('reference', 1000)->nullable()->change();

        });

        // Hapus index lama (jika ada)
        DB::statement('DROP INDEX visitor_logs_page_index ON visitor_logs');

        // Buat PREFIX INDEX (191 karakter aman untuk utf8mb4)
        DB::statement(
            'CREATE INDEX visitor_logs_page_index ON visitor_logs (page(191))'
        );
  }
 public function down(): void
    {

    }

};