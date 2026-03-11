<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::create('analytics_daily', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 150); // example.com
            $table->date('date');
            $table->string('type', 30);   // page_view, search, referrer, device, unique_page
            $table->string('key', 191);   // /berita, beasiswa, google.com, mobile
            $table->unsignedInteger('count')->default(0);

            $table->unique(['domain', 'date', 'type', 'key']);

            $table->index(['domain', 'type', 'date']);
            $table->index('key');
        });
  }
 public function down(): void
    {

    }

};