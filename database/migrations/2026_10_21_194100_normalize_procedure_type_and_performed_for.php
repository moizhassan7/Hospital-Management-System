<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $types = [
            'Surgical' => 'Major',
            'Diagnostic' => 'Minor',
            'Therapeutic' => 'Major',
        ];
        foreach ($types as $from => $to) {
            DB::table('procedures')->where('type', $from)->update(['type' => $to]);
        }

        DB::table('procedures')
            ->whereIn('performed_for', ['Inpatient', 'Outpatient', 'Both', 'Daycare'])
            ->update(['performed_for' => 'General']);

        DB::table('procedures')->where('name', 'Chemotherapy Session')->where('type', 'Major')->update(['type' => 'Minor']);
        DB::table('procedures')->where('name', 'Brain MRI')->update(['type' => 'Minor']);
        DB::table('procedures')->where('name', 'Newborn Screening')->update(['type' => 'Minor']);
    }

    public function down(): void
    {
        // Irreversible: original clinical categories were not stored separately.
    }
};
