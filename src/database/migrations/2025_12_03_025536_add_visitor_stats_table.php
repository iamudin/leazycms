<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::create('visitor_stats', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->date('date');
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('unique')->default(0);
            $table->timestamps();
            $table->unique(['domain', 'date']);
            $table->index(['domain', 'date']);
        });
  }
 public function down(): void
    {

    }

};