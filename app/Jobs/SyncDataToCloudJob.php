<?php

namespace App\Jobs;

use App\Models\LaboratoryPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncDataToCloudJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Only run on the local node
        if (config('sync.role') !== 'local') {
            return;
        }

        // 1. Check Internet Connectivity
        if (!$this->hasInternetConnection()) {
            return;
        }

        // 2. Fetch Unsynced Patients
        // Limit to 50 per batch to prevent overwhelming the payload size
        $patients = LaboratoryPatient::whereIn('sync_status', ['pending', 'failed'])
            ->with(['tests', 'sampleVials']) // Load relations if necessary, but test results is what we really want! Wait, TestResult model is what we need to sync. But let's check the schema.
            ->take(50)
            ->get();

        if ($patients->isEmpty()) {
            return;
        }

        // We also want to sync TestResult. However, TestResult belongs to LaboratoryPatient.
        // It's cleaner to sync patients first, then sync test results separately, OR bundle them.
        // Let's bundle them.
        $patients->load(['testResults']);

        // 3. Payload Construction
        $payload = $patients->map(function ($patient) {
            // Convert to array
            $data = $patient->toArray();
            
            // Format nested test_results
            if ($patient->testResults) {
                $data['test_results'] = $patient->testResults->toArray();
            } else {
                $data['test_results'] = [];
            }
            return $data;
        })->toArray();

        // 4. Send POST request to Cloud API Gateway
        try {
            $response = Http::timeout(45)
                ->withHeaders(['X-Sync-Token' => config('sync.cloud_token')])
                ->post(config('sync.cloud_api_url') . '/api/sync/push', [
                    'patients' => $payload
                ]);

            if ($response->successful()) {
                // 5. Mark as Synced
                $syncedIds = $patients->pluck('id');
                LaboratoryPatient::whereIn('id', $syncedIds)->update([
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);
                
                // Mark associated test results as synced as well if they were included
                foreach ($patients as $p) {
                    if ($p->testResults) {
                        $testResultIds = $p->testResults->pluck('id');
                        if ($testResultIds->isNotEmpty()) {
                            \App\Models\TestResult::whereIn('id', $testResultIds)->update([
                                'sync_status' => 'synced',
                                'synced_at' => now(),
                            ]);
                        }
                    }
                }
            } else {
                Log::warning("Cloud sync returned non-success status: " . $response->status(), ['response' => $response->body()]);
                LaboratoryPatient::whereIn('id', $patients->pluck('id'))->update([
                    'sync_status' => 'failed',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Cloud sync failed: " . $e->getMessage());
        }
    }

    private function hasInternetConnection(): bool
    {
        try {
            $connected = @fsockopen("www.google.com", 80);
            if ($connected) {
                fclose($connected);
                return true;
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return false;
    }
}
