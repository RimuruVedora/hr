<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SyncSetting;
use App\Models\EssRequest;
use App\Models\Competency; // Import Competency model
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function index()
    {
        $settings = SyncSetting::first();
        if (!$settings) {
            $settings = SyncSetting::create([
                'sync_mode' => 'manual',
                'remote_url' => 'https://hr2.viahale.com'
            ]);
        }
        return view('admin.sync', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'sync_mode' => 'required|in:manual,auto',
            'remote_url' => 'nullable|url',
            'api_token' => 'nullable|string',
        ]);

        $settings = SyncSetting::first();
        $settings->update($request->all());

        return response()->json(['success' => true, 'message' => 'Settings updated successfully.']);
    }

    public function syncNow()
    {
        $result = self::executeSync();
        return response()->json($result);
    }

    public static function executeSync()
    {
        $settings = SyncSetting::first();
        
        if (!$settings || !$settings->remote_url || !$settings->api_token) {
            return ['success' => false, 'message' => 'Remote URL and API Token are required.'];
        }

        try {
            // Gather data to sync
            $payload = [
                'ess_requests' => EssRequest::all()->toArray(),
                'competencies' => Competency::all()->toArray(),
            ];

            $response = Http::withToken($settings->api_token)
                ->post(rtrim($settings->remote_url, '/') . '/api/sync/receive', $payload);

            if ($response->successful()) {
                $settings->update([
                    'last_synced_at' => now(),
                    'last_sync_status' => 'success',
                    'last_sync_message' => 'Data synced successfully.'
                ]);
                return ['success' => true, 'message' => 'Sync completed successfully.'];
            } else {
                $errorMsg = 'Remote server error: ' . $response->status() . ' - ' . $response->body();
                $settings->update([
                    'last_synced_at' => now(),
                    'last_sync_status' => 'failed',
                    'last_sync_message' => $errorMsg
                ]);
                return ['success' => false, 'message' => $errorMsg];
            }

        } catch (\Exception $e) {
            $settings->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
                'last_sync_message' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Sync error: ' . $e->getMessage()];
        }
    }

    public function receiveData(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            if (isset($data['ess_requests'])) {
                foreach ($data['ess_requests'] as $item) {
                    EssRequest::updateOrCreate(
                        ['id' => $item['id']], // Match by ID
                        $item // Update all fields
                    );
                }
            }

            if (isset($data['competencies'])) {
                foreach ($data['competencies'] as $item) {
                    Competency::updateOrCreate(
                        ['id' => $item['id']], // Match by ID
                        $item // Update all fields
                    );
                }
            }

            // Handle other models...

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data received and processed.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sync Receive Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
