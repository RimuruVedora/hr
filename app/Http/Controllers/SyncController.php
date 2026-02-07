<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SyncSetting;
use App\Models\EssRequest;
use App\Models\Competency; // Import Competency model
use App\Models\Employee;
use App\Models\User;
use App\Models\Account;
use App\Models\Department;
use App\Models\JobRole;
use Illuminate\Support\Facades\Hash;
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

    public function syncEmployee(Request $request)
    {
        // 1. Verify Token
        $token = $request->header('Authorization');
        // Extract 'Bearer ' if present
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        $validToken = env('EMPLOYEE_SYNC_TOKEN');
        if (!$validToken || $token !== $validToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Validate Input
        $validated = $request->validate([
            'employee_id' => 'required|string',
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'job_role' => 'nullable|string',
            'department' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);

        try {
            DB::beginTransaction();

            // 3. Resolve Relations
            $deptId = null;
            if (!empty($validated['department'])) {
                $dept = Department::firstOrCreate(['name' => $validated['department']]);
                $deptId = $dept->id;
            }

            $roleId = null;
            if (!empty($validated['job_role'])) {
                $role = JobRole::firstOrCreate(['name' => $validated['job_role']]);
                $roleId = $role->id;
            }

            // 4. Update or Create Account
            $account = Account::where('email', $validated['email'])
                              ->orWhere('User_ID', $validated['employee_id'])
                              ->first();

            if (!$account) {
                $account = new Account();
                $account->User_ID = $validated['employee_id'];
                $account->email = $validated['email']; // lowercase per DB inspection
                $account->password = Hash::make($validated['password'] ?? 'password123');
            }

            // Update fields
            $account->name = $validated['first_name'] . ' ' . $validated['last_name'];
            $account->department_id = $deptId;
            $account->job_role_id = $roleId;
            $account->position = $validated['job_role'] ?? 'Employee';
            $account->Account_Type = 2; // Default to Employee
            $account->active = 'active'; // Default active
            
            // Set required defaults if new
            if (!$account->exists) {
                $account->theme_mode = 'light';
                $account->ai_zoho_enabled = 0;
                $account->ai_gemini_enabled = 0;
                $account->login_count = 0;
                $account->ai_zoho_budget_calc_enabled = 0;
            }
            
            // Update password if provided
            if (!empty($validated['password'])) {
                $account->password = Hash::make($validated['password']);
            }

            $account->save();

            // 5. Update or Create Employee Record
            $employee = Employee::where('employee_id', $validated['employee_id'])
                                ->orWhere('account_id', $account->Login_ID)
                                ->first();

            if (!$employee) {
                $employee = new Employee();
                $employee->employee_id = $validated['employee_id'];
                $employee->account_id = $account->Login_ID;
            }

            $employee->first_name = $validated['first_name'];
            $employee->last_name = $validated['last_name'];
            $employee->email = $validated['email'];
            $employee->department = $validated['department'] ?? null; // Employee model stores department name string based on fillable
            $employee->job_role_id = $roleId;
            $employee->status = 'Active'; // Default status
            
            // Handle date_hired if provided in future updates, or default
            // $employee->date_hired = now(); 

            $employee->save();

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Employee and Account synced successfully.',
                'data' => [
                    'account' => $account,
                    'employee' => $employee
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee Sync Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function receiveData(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            Log::info('Sync data received', $data);

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
