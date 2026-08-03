<?php

namespace App\Services;

use App\Models\LabReportDoctor;
use App\Models\LabSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HospitalBrandingService
{
    private const CACHE_KEY = 'hospital.branding.settings';

    private const REPORT_DOCTORS_CACHE_KEY = 'lab.report_doctors.active';

  private const KEYS = [
        'name',
        'short_name',
        'tagline',
        'city',
        'address',
        'phone',
        'email',
        'logo',
        'header_image',
        'footer_image',
    ];

    public function isAvailable(): bool
    {
        return Schema::hasTable('lab_settings') && Schema::hasColumn('lab_settings', 'key');
    }

    public function all(): array
    {
        if (! $this->isAvailable()) {
            return $this->defaults();
        }

        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $stored = LabSetting::query()->pluck('value', 'key')->all();
            $merged = array_merge($this->defaults(), $stored);

            return array_intersect_key($merged, array_flip(self::KEYS));
        });
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $all = $this->all();

        return $all[$key] ?? $default ?? config("hospital.{$key}");
    }

    public function logoUrl(): string
    {
        $logo = $this->get('logo');

        return $logo ? asset($logo) : asset(config('hospital.logo'));
    }

    public function logoPath(): string
    {
        $resolved = $this->resolveImagePath($this->get('logo'));

        return $resolved ?? public_path(config('hospital.logo'));
    }

    public function resolveImagePath(?string $storedValue): ?string
    {
        if (empty($storedValue)) {
            return null;
        }

        $relativePath = str_replace('storage/', '', $storedValue);
        $storagePath = storage_path('app/public/' . $relativePath);
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        $publicPath = public_path($storedValue);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        if (file_exists($storedValue)) {
            return $storedValue;
        }

        return null;
    }

    public function applyToConfig(): void
    {
        $settings = $this->all();

        foreach (self::KEYS as $key) {
            if (! empty($settings[$key])) {
                config(["hospital.{$key}" => $settings[$key]]);
            }
        }
    }

    public function updateBranding(array $data, ?UploadedFile $logo = null, ?UploadedFile $headerImage = null, ?UploadedFile $footerImage = null): void
    {
        foreach (['name', 'short_name', 'tagline', 'city', 'address', 'phone', 'email'] as $key) {
            if (array_key_exists($key, $data)) {
                LabSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $data[$key]]
                );
            }
        }

        if ($logo) {
            $this->storeImage($logo, 'logo');
        }
        if ($headerImage) {
            $this->storeImage($headerImage, 'header_image');
        }
        if ($footerImage) {
            $this->storeImage($footerImage, 'footer_image');
        }

        $this->clearCache();
        $this->clearReportDoctorsCache();
        $this->applyToConfig();
    }

    public function storeImage(UploadedFile $file, string $settingKey): void
    {
        $directory = 'lab/branding';
        Storage::disk('public')->makeDirectory($directory);

        $current = LabSetting::where('key', $settingKey)->value('value');
        if ($current && str_starts_with($current, 'storage/')) {
            $oldPath = str_replace('storage/', '', $current);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $extension = 'png';
        }

        $filename = $settingKey . '_' . time() . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
        $relativePath = $directory . '/' . $filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        if (! $this->optimizeAndSaveImage($file->getPathname(), $absolutePath, $extension)) {
            $stored = $file->storeAs($directory, $filename, 'public');
            if (! $stored) {
                throw new \RuntimeException("{$settingKey} could not be saved to storage.");
            }
            $relativePath = $stored;
        }

        LabSetting::updateOrCreate(
            ['key' => $settingKey],
            ['value' => 'storage/' . $relativePath]
        );
    }

    private function optimizeAndSaveImage(string $source, string $destination, string $extension): bool
    {
        if (! function_exists('imagecreatefromstring')) {
            return false;
        }

        $contents = @file_get_contents($source);
        if ($contents === false) {
            return false;
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxDimension = 1200;

        if ($width > $maxDimension || $height > $maxDimension) {
            $ratio = min($maxDimension / $width, $maxDimension / $height);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
            $resized = imagecreatetruecolor($newWidth, $newHeight);

            if (in_array($extension, ['png', 'gif', 'webp'], true)) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        $saved = match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $destination, 90),
            'gif' => imagegif($image, $destination),
            'webp' => function_exists('imagewebp') ? imagewebp($image, $destination, 90) : false,
            default => imagepng($image, $destination, 6),
        };

        imagedestroy($image);

        return (bool) $saved;
    }

    /** @return Collection<int, LabReportDoctor> */
    public function activeReportDoctors(): Collection
    {
        if (! Schema::hasTable('lab_report_doctors')) {
            return collect();
        }

        return Cache::remember(self::REPORT_DOCTORS_CACHE_KEY, 3600, function () {
            return LabReportDoctor::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    public function createReportDoctor(array $data): LabReportDoctor
    {
        $doctor = LabReportDoctor::create($data);

        $this->clearReportDoctorsCache();

        return $doctor;
    }

    public function updateReportDoctor(LabReportDoctor $doctor, array $data): LabReportDoctor
    {
        $doctor->update($data);

        $this->clearReportDoctorsCache();

        return $doctor->fresh();
    }

    public function deleteReportDoctor(LabReportDoctor $doctor): void
    {
        $doctor->delete();

        $this->clearReportDoctorsCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function clearReportDoctorsCache(): void
    {
        Cache::forget(self::REPORT_DOCTORS_CACHE_KEY);
    }

    /**
     * Whether the branding settings cache is already warm. Used to skip the
     * per-request DB seeding check on normal web requests.
     */
    public function isCacheWarm(): bool
    {
        return Cache::has(self::CACHE_KEY);
    }

    public function seedDefaultsIfEmpty(): void
    {
        if (! $this->isAvailable() || LabSetting::query()->exists()) {
            return;
        }

        foreach ($this->defaults() as $key => $value) {
            if ($value !== null && $value !== '') {
                LabSetting::create(['key' => $key, 'value' => $value]);
            }
        }

        $this->clearCache();
    }

    private function defaults(): array
    {
        return [
            'name' => config('hospital.name'),
            'short_name' => config('hospital.short_name'),
            'tagline' => config('hospital.tagline'),
            'city' => config('hospital.city'),
            'address' => config('hospital.address', ''),
            'phone' => config('hospital.phone', ''),
            'email' => config('hospital.email', ''),
            'logo' => config('hospital.logo'),
            'header_image' => '',
            'footer_image' => '',
        ];
    }
}
