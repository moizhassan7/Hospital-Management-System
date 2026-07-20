<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryPatient;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloudSyncController extends Controller
{
    public function receiveSyncPayload(Request $request)
    {
        // 1. Validate Sync Authentication Token
        if ($request->header('X-Sync-Token') !== config('sync.cloud_token')) {
            Log::warning("CloudSync: Unauthorized attempt", ['ip' => $request->ip()]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Only process if this is the cloud role (optional check, but good practice)
        if (config('sync.role') !== 'cloud') {
            return response()->json(['message' => 'This node is not configured as a cloud server'], 403);
        }

        $patientsData = $request->input('patients', []);

        if (empty($patientsData)) {
            return response()->json(['message' => 'No data provided', 'count' => 0]);
        }

        try {
            DB::transaction(function () use ($patientsData) {
                foreach ($patientsData as $data) {
                    
                    if (empty($data['sync_id'])) {
                        continue;
                    }

                    // Extract test_results
                    $testResultsData = $data['test_results'] ?? [];
                    unset($data['test_results']);

                    // Force sync status back to synced on the cloud node
                    $data['sync_status'] = 'synced';
                    $data['synced_at'] = now();

                    // Upsert Patient (Update if exists by sync_id, Insert if new)
                    $patient = LaboratoryPatient::updateOrCreate(
                        ['sync_id' => $data['sync_id']],
                        $data
                    );

                    // Sync associated test results
                    if (!empty($testResultsData)) {
                        foreach ($testResultsData as $resultData) {
                            if (empty($resultData['sync_id'])) {
                                continue;
                            }
                            
                            $resultData['laboratory_patient_id'] = $patient->id; // Ensure FK is correct
                            $resultData['sync_status'] = 'synced';
                            $resultData['synced_at'] = now();

                            TestResult::updateOrCreate(
                                ['sync_id' => $resultData['sync_id']],
                                $resultData
                            );
                        }
                    }
                }
            });

            return response()->json(['message' => 'Sync successful', 'count' => count($patientsData)]);
        } catch (\Exception $e) {
            Log::error("CloudSync: Transaction failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Sync transaction failed', 'error' => $e->getMessage()], 500);
        }
    }
}
