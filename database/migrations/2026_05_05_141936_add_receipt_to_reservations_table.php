<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'receipt_no')) {
                $table->string('receipt_no')->nullable()->after('invoice_path');
            }
            if (! Schema::hasColumn('reservations', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('receipt_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['receipt_no', 'receipt_path']);
        });
    }
};
