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
            $table->string('theme')->nullable();
            $table->json('modules')->nullable();
            $table->boolean('custom_theme')->default(false);
            $table->enum('status',['active','maintenance','suspended'])->default('active');
            $table->timestamps();
        });


        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('name')->nullable();
            $table->string('preview')->nullable();
            $table->string('git')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
            $table->timestamps();
        });
        foreach(['posts','categories','tags','analytics_visitors','analytics_daily','options','polling_topics','roles','users','one_time_tokens'] as $tableName) {
            if (!Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->index();
                });
            }
        }

        Schema::table('options', function (Blueprint $table) {
            $table->dropUnique('options_name_unique');
            $table->unique(['tenant_id', 'name']);
        });

        $mainDomain = parse_url(main_domain(), PHP_URL_HOST);
        if ($mainDomain) {
            $tenant = \Leazycms\Web\Models\Tenant::firstOrCreate(
                ['domain' => $mainDomain],
                [
                    'name' => 'Main Site',
                    'status' => 'active',
                    'custom_theme' => 0,
                    'theme' => null
                ]
            );

            \Leazycms\Web\Models\User::where('level', 'admin')->update([
                'tenant_id' => $tenant->id,
                'host' => $mainDomain
            ]);
        }

        \Leazycms\Web\Http\Controllers\TenantController::deleteOptionsDefault();
        rewrite_env(['MULTITENANT_INSTALLED'=>true]);
        
    }

    public function down(): void
    {
        if (!config('modules.multisite_enabled')) {
            return;
        }

        Schema::dropIfExists('tenants');
    }
};
