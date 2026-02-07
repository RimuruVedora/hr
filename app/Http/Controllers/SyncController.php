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
        $expectedToken = env('EMPLOYEE_SYNC_TOKEN', 'secret_token_12345');
        $bearerToken = $request->bearerToken();

        if ($bearerToken !== $expectedToken) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // 2. Validate Request
        try {
            $validated = $request->validate([
                'employee_id' => 'required|string',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'department' => 'nullable|string',
                'job_role' => 'nullable|string',
                'password' => 'nullable|string', // Optional password update
                'date_hired' => 'nullable|date',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
             return response()->json(['success' => false, 'message' => 'Validation Error: ' . $e->getMessage(), 'errors' => $e->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // 3. Resolve Job Role & Department
            $roleId = null;
            if (!empty($validated['job_role'])) {
                $jobRole = JobRole::firstOrCreate(['job_role' => $validated['job_role']]);
                $roleId = $jobRole->job_role_id;
            }

            if (!empty($validated['department'])) {
                Department::firstOrCreate(['name' => $validated['department']]);
            }

            // 4. Update or Create Account
            $account = Account::where('User_ID', $validated['employee_id'])
                              ->orWhere('Email', $validated['email'])
                              ->first();

            if (!$account) {
                $account = new Account();
                $account->User_ID = $validated['employee_id'];
                $account->Email = $validated['email'];
                $account->Password = Hash::make($validated['password'] ?? 'password123'); // Default password if new
                $account->Account_Type = 2; // Default to Employee
            }

            $account->First_Name = $validated['first_name'];
            $account->Last_Name = $validated['last_name'];
            // Only update password if provided
            if (!empty($validated['password'])) {
                $account->Password = Hash::make($validated['password']);
            }
            $account->Status = 'Active';

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
            $employee->department = $validated['department'] ?? null;
            $employee->job_role_id = $roleId;
            $employee->status = 'Active';
            
            if (isset($validated['date_hired'])) {
                $employee->date_hired = $validated['date_hired'];
            }

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
            Log::error('Sync Receive Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function listEmployees(Request $request)
    {
        // 1. Verify Token (Optional: but recommended for data privacy)
        $expectedToken = env('EMPLOYEE_SYNC_TOKEN', 'secret_token_12345');
        // Allow check via Bearer token or 'token' query param for browser ease
        $token = $request->bearerToken() ?? $request->query('token');

        if ($token !== $expectedToken) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized. Please provide valid token via Bearer Auth or ?token= param.'
            ], 401);
        }

        // 2. Fetch Data
        // Get all employees with their related account info
        $employees = Employee::with('account')->get();
        
        // Also get accounts that might not have an employee record yet (orphaned accounts)
        $accounts = Account::whereDoesntHave('employee')->get();

        return response()->json([
            'success' => true,
            'count_employees' => $employees->count(),
            'count_orphaned_accounts' => $accounts->count(),
            'employees' => $employees,
            'orphaned_accounts' => $accounts
        ]);
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
