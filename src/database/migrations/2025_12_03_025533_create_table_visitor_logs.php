<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
Schema::create('visitor_logs', function (Blueprint $table) {
$table->id();
$table->unsignedBigInteger('visitor_id')->index();
$table->string('page', 255)->index();
$table->string('reference', 255)->nullable();
$table->unsignedBigInteger('post_id')->nullable();
$table->smallInteger('status_code')->default(200);
$table->integer('tried')->default(1);
$table->timestamps();

$table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('cascade');
});
}

public function down(): void
{
Schema::dropIfExists('visitor_logs');
}
};