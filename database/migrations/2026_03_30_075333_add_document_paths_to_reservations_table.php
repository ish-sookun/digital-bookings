<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'reference')) {
                $table->string('reference')->nullable()->after('id');
            }
            if (! Schema::hasColumn('reservations', 'purchase_order_path')) {
                $table->string('purchase_order_path')->nullable()->after('purchase_order_no');
            }
            if (! Schema::hasColumn('reservations', 'invoice_path')) {
                $table->string('invoice_path')->nullable()->after('invoice_no');
            }
            if (! Schema::hasColumn('reservations', 'signed_ro_path')) {
                $table->string('signed_ro_path')->nullable()->after('invoice_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['reference', 'purchase_order_path', 'invoice_path', 'signed_ro_path']);
        });
    }
};
