<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->string('reference',1000)->nullable()->change();
            $table->string('page',1000)->change();
        
        });
  }
 public function down(): void
    {

    }

};