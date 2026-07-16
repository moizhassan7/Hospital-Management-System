<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop desktop-related tables
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('sync_cursors');
        Schema::dropIfExists('lab_order_tests');
        Schema::dropIfExists('lab_orders');

        // Drop desktop-related columns from tests
        if (Schema::hasColumn('tests', 'desktop_test_id')) {
            Schema::table('tests', function (Blueprint $table) {
                $table->dropUnique(['desktop_test_id']);
                $table->dropColumn(['desktop_test_id', 'source_hash', 'source_updated_at']);
            });
        }

        // Drop desktop-related columns from test_particulars
        if (Schema::hasColumn('test_particulars', 'desktop_particular_id')) {
            Schema::table('test_particulars', function (Blueprint $table) {
                $table->dropUnique(['desktop_particular_id']);
                $table->dropColumn(['desktop_particular_id', 'source_hash', 'source_updated_at']);
            });
        }

        // Drop desktop-related columns from laboratory_patients
        if (Schema::hasColumn('laboratory_patients', 'desktop_invoice')) {
            Schema::table('laboratory_patients', function (Blueprint $table) {
                $table->dropIndex(['mr_no', 'desktop_invoice']);
                $table->dropColumn('desktop_invoice');
            });
        }
    }

    public function down(): void
    {
        // No-op or recreate if needed. Since we are removing desktop sync completely, down is not needed.
    }
};
