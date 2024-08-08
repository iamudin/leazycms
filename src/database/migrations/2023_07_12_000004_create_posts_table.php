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
        Schema::create('posts', function (Blueprint $table) {
            $table->id()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->index()->nullable();
            $table->string('parent_type')->index()->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->string('status',50)->default('draft')->index();
            $table->string('type')->index();
            $table->string('mime')->nullable();
            $table->text('title')->nullable();
            $table->char('pinned',1)->default('0');
            $table->longText('content')->nullable();
            $table->text('short_content')->nullable();
            $table->string('slug',255)->index()->nullable();
            $table->string('keyword',300)->nullable();
            $table->string('description',600)->nullable();
            $table->string('media',500)->nullable();
            $table->text('media_description')->nullable();
            $table->string('url',500)->nullable();
            $table->text('redirect_to')->nullable();
            $table->char('allow_comment',1)->default(0);
            $table->tinyInteger('comment_count')->default(0);
            $table->tinyInteger('sort')->default(0);
            $table->json('data_field')->nullable();
            $table->json('data_loop')->nullable();
            $table->string('password')->nullable();
            $table->tinyInteger('deleteable')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
