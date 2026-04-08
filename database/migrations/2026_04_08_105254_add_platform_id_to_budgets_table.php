<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique(['year', 'month']);
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('platform_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $defaultPlatformId = DB::table('platforms')->orderBy('id')->value('id');

        if ($defaultPlatformId !== null) {
            DB::table('budgets')->whereNull('platform_id')->update(['platform_id' => $defaultPlatformId]);
        } else {
            DB::table('budgets')->delete();
        }

        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('platform_id')->nullable(false)->change();
            $table->unique(['platform_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique(['platform_id', 'year', 'month']);
            $table->dropConstrainedForeignId('platform_id');
            $table->unique(['year', 'month']);
        });
    }
};
