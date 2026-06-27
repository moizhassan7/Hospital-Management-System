<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Models\Test;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.twilio.enabled')
            && config('services.twilio.sid')
            && config('services.twilio.token')
            && config('services.twilio.whatsapp_from');
    }

    public function sendLabResult(LaboratoryPatient $patient, Test $test, string $pdfUrl): bool
    {
        if (!$this->isEnabled()) {
            Log::info('WhatsApp skipped: Twilio is not configured.');

            return false;
        }

        $phone = $this->formatWhatsAppNumber($patient->contact_no);

        if (!$phone) {
            Log::warning('WhatsApp skipped: no valid contact number for patient ' . $patient->id);

            return false;
        }

        $message = $this->buildLabResultMessage($patient, $test, $pdfUrl);

        return $this->sendMessage($phone, $message, $pdfUrl);
    }

    public function buildLabResultMessage(LaboratoryPatient $patient, Test $test, string $pdfUrl): string
    {
        $hospital = config('hospital.name', config('app.name', 'Hospital'));

        return implode("\n", [
            "Assalam-o-Alaikum {$patient->patient_name},",
            '',
            "Your pathology test result is ready at {$hospital}.",
            '',
            "Test: {$test->name}",
            'MR No: ' . ($patient->mr_no ?? 'N/A'),
            'Date: ' . now()->format('d-M-Y h:i A'),
            '',
            "Download PDF: {$pdfUrl}",
            '',
            'For a printed copy, visit the front desk with your phone number or MR number.',
        ]);
    }

    private function sendMessage(string $to, string $body, ?string $mediaUrl = null): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');

        $payload = [
            'From' => 'whatsapp:' . $from,
            'To' => 'whatsapp:' . $to,
            'Body' => $body,
        ];

        if ($mediaUrl && $this->isPublicUrl($mediaUrl)) {
            $payload['MediaUrl'] = $mediaUrl;
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", $payload);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['to' => $to, 'sid' => $response->json('sid')]);

                return true;
            }

            Log::error('WhatsApp send failed', [
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp exception: ' . $e->getMessage());
        }

        return false;
    }

    private function formatWhatsAppNumber(?string $contact): ?string
    {
        if (!$contact) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $contact);

        if ($digits === '') {
            return null;
        }

        $defaultCountry = config('services.twilio.default_country_code', '92');

        if (str_starts_with($digits, '0')) {
            $digits = $defaultCountry . substr($digits, 1);
        }

        if (!str_starts_with($digits, $defaultCountry) && strlen($digits) <= 10) {
            $digits = $defaultCountry . $digits;
        }

        return '+' . $digits;
    }

    private function isPublicUrl(string $url): bool
    {
        return str_starts_with($url, 'https://')
            && !str_contains($url, 'localhost')
            && !str_contains($url, '127.0.0.1');
    }
}
