<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Str;

class EmployeesFromAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {
            // Skip if account has no email (shouldn't happen but good for safety)
            if (empty($account->email)) {
                continue;
            }

            // Derive names from email
            $emailParts = explode('@', $account->email);
            $localPart = $emailParts[0];
            
            // Try to split by dot or underscore
            $nameParts = preg_split('/[._]/', $localPart);
            $firstName = ucfirst($nameParts[0] ?? 'User');
            $lastName = isset($nameParts[1]) ? ucfirst($nameParts[1]) : 'Employee';

            // Get Department Name
            $departmentName = null;
            if ($account->department_id) {
                $department = Department::find($account->department_id);
                if ($department) {
                    $departmentName = $department->name;
                }
            }

            // Create or Update Employee
            // Match by email to avoid duplicates and preserve existing IDs
            $employee = Employee::where('email', $account->email)->first();

            if ($employee) {
                // Update existing employee
                $employee->update([
                    'account_id' => $account->Login_ID,
                    // Optionally update other fields if needed, but prioritize existing data?
                    // Let's update fields that might be missing or should match account
                    'job_role_id' => $account->job_role_id, 
                    'department' => $departmentName ?? $employee->department,
                ]);
            } else {
                // Create new employee
                Employee::create([
                    'employee_id' => (string) $account->Login_ID,
                    'account_id' => $account->Login_ID,
                    'email' => $account->email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'department' => $departmentName,
                    'job_role_id' => $account->job_role_id,
                    'status' => 'Active',
                    'date_hired' => now(),
                ]);
            }
        }
    }
}
