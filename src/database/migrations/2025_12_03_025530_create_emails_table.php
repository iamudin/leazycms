<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            // informasi email
            $table->string('from')->nullable();        // pengirim
            $table->string('to');                      // penerima
            $table->string('cc')->nullable();          // cc
            $table->string('bcc')->nullable();         // bcc 
            $table->string('subject')->nullable();     // subjek
            $table->longText('body')->nullable();      // isi email
            // lampiran (opsional)
            $table->string('attachment_path')->nullable();
            // status pengiriman/penerimaan
            $table->enum('direction', ['inbox', 'outbox'])->nullable(); // masuk/keluar
            $table->enum('status', ['pending', 'sent', 'failed', 'received'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
