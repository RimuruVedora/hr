<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Account;
use App\Models\JobRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmployeeSyncController extends Controller
{
    public function sync(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('Employee Sync Request:', $request->all());

        $validated = $request->validate([
            'employee_id' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'job_role' => 'nullable|string', // Name of the job role
            'department' => 'nullable|string',
            'status' => 'nullable|string',
            'date_hired' => 'nullable|date',
            // Optional: You can send a password or we can generate a default one
            'password' => 'nullable|string|min:8', 
        ]);

        try {
            // 1. Handle Job Role (Find or Create)
            $jobRoleId = null;
            if (!empty($validated['job_role'])) {
                $jobRole = JobRole::firstOrCreate(
                    ['name' => $validated['job_role']],
                    ['description' => 'Imported via API', 'weighting' => 0]
                );
                $jobRoleId = $jobRole->id;
            }

            // 2. Create or Update Account (for Login)
            // Assuming email is unique identifier for account
            $accountData = [
                'Name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'Password' => isset($validated['password']) ? Hash::make($validated['password']) : (Account::where('Email', $validated['email'])->exists() ? Account::where('Email', $validated['email'])->value('Password') : Hash::make('password123')), 
                'Account_Type' => 2, // Default to Employee (2)
                'position' => $validated['job_role'] ?? 'Employee', // Default position
            ];

            // Check if account exists to determine if we need to set User_ID
            $existingAccount = Account::where('Email', $validated['email'])->first();
            if (!$existingAccount) {
                // Generate a unique User_ID for new accounts
                $accountData['User_ID'] = 'USR-' . strtoupper(Str::random(8));
            }

            $account = Account::updateOrCreate(
                ['Email' => $validated['email']],
                $accountData
            );

            // 3. Create or Update Employee Record
            $employee = Employee::updateOrCreate(
                ['employee_id' => $validated['employee_id']],
                [
                    'account_id' => $account->Login_ID,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'department' => $validated['department'] ?? 'General',
                    'job_role_id' => $jobRoleId,
                    'status' => $validated['status'] ?? 'Active',
                    'date_hired' => $validated['date_hired'] ?? now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Employee synced successfully',
                'data' => [
                    'employee' => $employee,
                    'account_id' => $account->Login_ID
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Employee Sync Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync employee: ' . $e->getMessage()
            ], 500);
        }
    }
}
