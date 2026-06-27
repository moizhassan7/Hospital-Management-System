<?php

use App\Models\LabReportDoctor;
use App\Models\LabSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lab_settings')) {
            Schema::create('lab_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (Schema::hasColumn('lab_settings', 'key')) {
            return;
        }

        $legacy = DB::table('lab_settings')->orderBy('id')->first();

        Schema::drop('lab_settings');

        Schema::create('lab_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        if (! $legacy) {
            return;
        }

        $logo = $legacy->logo_path ?? null;
        if ($logo && ! str_starts_with($logo, 'storage/')) {
            $logo = 'storage/' . ltrim($logo, '/');
        }

        $pairs = [
            'name' => $legacy->lab_name ?? null,
            'address' => $legacy->address ?? null,
            'logo' => $logo,
        ];

        foreach ($pairs as $key => $value) {
            if ($value !== null && $value !== '') {
                LabSetting::create(['key' => $key, 'value' => $value]);
            }
        }

        if (! empty($legacy->hematologists) && LabReportDoctor::query()->doesntExist()) {
            $doctors = json_decode($legacy->hematologists, true);

            if (is_array($doctors)) {
                foreach ($doctors as $index => $doctor) {
                    if (! is_array($doctor) || empty($doctor['name'])) {
                        continue;
                    }

                    LabReportDoctor::create([
                        'name' => $doctor['name'],
                        'designation' => $doctor['designation'] ?? null,
                        'qualifications' => $doctor['qualifications'] ?? null,
                        'phone' => $doctor['phone'] ?? null,
                        'sort_order' => $index,
                        'is_active' => true,
                    ]);
                }
            }
        }

        Cache::forget('hospital.branding.settings');
    }

    public function down(): void
    {
        // Irreversible: legacy single-row schema is not restored.
    }
};
