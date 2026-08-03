<?php

namespace App\Http\Controllers;

use App\Models\LabReportDoctor;
use App\Services\HospitalBrandingService;
use Illuminate\Http\Request;

class LabSettingsController extends Controller
{
    public function __construct(
        private HospitalBrandingService $branding
    ) {}

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'branding');
        $settings = $this->branding->all();
        $doctors = LabReportDoctor::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('laboratory.settings.index', compact('tab', 'settings', 'doctors'));
    }

    public function updateBranding(Request $request)
    {
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            if (! $logo->isValid()) {
                return back()
                    ->withErrors(['logo' => $this->logoUploadErrorMessage($logo->getError())])
                    ->withInput($request->except('logo'));
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:120'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'],
            'header_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'],
            'footer_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'],
        ], [
            'logo.mimes' => 'Logo must be a PNG, JPG, GIF, or WebP file.',
            'logo.max' => 'Logo is too large (max 10MB). Compress the image or save as JPG.',
            'header_image.mimes' => 'Header must be a PNG, JPG, GIF, or WebP file.',
            'header_image.max' => 'Header is too large (max 10MB).',
            'footer_image.mimes' => 'Footer must be a PNG, JPG, GIF, or WebP file.',
            'footer_image.max' => 'Footer is too large (max 10MB).',
        ]);

        $this->branding->updateBranding(
            $validated, 
            $request->file('logo'),
            $request->file('header_image'),
            $request->file('footer_image')
        );

        return redirect()
            ->route('pathology.settings.index', ['tab' => 'branding'])
            ->with('success', 'Lab branding updated. Changes apply across the entire website.');
    }

    private function logoUploadErrorMessage(int $error): string
    {
        $limit = ini_get('upload_max_filesize') ?: '2M';

        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "Logo file is too large for the server (limit: {$limit}). Compress MM Logo.png, save as JPG, or ask admin to raise PHP upload_max_filesize.",
            UPLOAD_ERR_PARTIAL => 'Logo upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Server could not save the logo file. Contact your system administrator.',
            default => 'Logo could not be uploaded. Try a smaller PNG/JPG file.',
        };
    }

    public function storeDoctor(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'qualifications' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $this->branding->createReportDoctor($validated);

        return redirect()
            ->route('pathology.settings.index', ['tab' => 'doctors'])
            ->with('success', 'Doctor added to lab reports.');
    }

    public function updateDoctor(Request $request, LabReportDoctor $doctor)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'qualifications' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $this->branding->updateReportDoctor($doctor, $validated);

        return redirect()
            ->route('pathology.settings.index', ['tab' => 'doctors'])
            ->with('success', 'Doctor updated.');
    }

    public function destroyDoctor(LabReportDoctor $doctor)
    {
        $this->branding->deleteReportDoctor($doctor);

        return redirect()
            ->route('pathology.settings.index', ['tab' => 'doctors'])
            ->with('success', 'Doctor removed from lab reports.');
    }
}
