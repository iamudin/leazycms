<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!config('modules.multisite_enabled')) {
            return;
        }

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->enum('status',['active','maintenance','suspended'])->default('active');
            $table->timestamps();
        });
        foreach(['posts','categories','tags','analytics_visitors','analytics_daily','options','polling_topics','roles','users'] as $tableName) {
            if (!Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->index();
                });
            }
        }

        Schema::table('options', function (Blueprint $table) {

            $table->dropUnique('options_name_unique');
            // tambah composite unique
            $table->unique(['tenant_id', 'name']);
        });

    }

    public function down(): void
    {
        if (!config('modules.multisite_enabled')) {
            return;
        }

        Schema::dropIfExists('tenants');
    }
};