<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->string('slug')->index();
            $table->string('photo')->nullable();
            $table->string('level')->default('admin');
            $table->string('url')->nullable();
            $table->string('host')->nullable();
            $table->string('active_session')->nullable();
            $table->json('user_meta')->nullable();
            $table->enum('status',['active','blocked'])->default('blocked');
            $table->string('last_login_ip')->nullable();
            $table->datetime('last_login_at')->nullable();
            $table->softDeletes();
        });

    }

    // /**
    //  * Reverse the migrations.
    //  *
    //  * @return void
    //  */
    public function down()
    {
            Schema::table('users', function (Blueprint $table) {
                $table->Uuid('id')->change();
            $table->string('username')->unique();
            $table->string('slug')->index();
                $table->string('photo')->nullable();
                $table->string('level')->default('admin');
                $table->string('url')->unique();
                $table->string('status');
                $table->string('last_login_ip')->nullable();
                $table->datetime('last_login_at')->nullable();
            });
    }
};
