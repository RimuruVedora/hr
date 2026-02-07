<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EssRequest;
use App\Models\Employee;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EssRequestApiController extends Controller
{
    public function getExternalRequests(Request $request)
    {
        // Token Check
        $token = $request->header('Authorization');
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }
        
        if (!$token || $token !== env('ESS_API_TOKEN')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $requests = EssRequest::with('employee')->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $requests]);
    }

    public function storeExternalRequest(Request $request)
    {
        // Token Check
        $token = $request->header('Authorization');
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        if (!$token || $token !== env('ESS_API_TOKEN')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'category' => 'required|string',
            'type' => 'required|string',
            'details' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Find Employee
        // Try finding by Account User_ID first
        $account = Account::where('User_ID', $request->user_id)->first();
        $employee = null;

        if ($account && $account->employee) {
            $employee = $account->employee;
        } else {
            // Fallback: try finding employee directly by employee_id field
            $employee = Employee::where('employee_id', $request->user_id)->first();
        }

        if (!$employee) {
             return response()->json(['error' => 'Employee not found for User ID: ' . $request->user_id], 404);
        }

        $essRequest = EssRequest::create([
            'employee_id' => $employee->id,
            'request_category' => $request->category,
            'request_type' => $request->type,
            'details' => $request->details ?? [],
            'status' => 'Pending',
        ]);

        return response()->json(['status' => 'success', 'data' => $essRequest], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure user is an employee (Account_Type 2)
        if ($user->Account_Type != 2) {
             return response()->json(['error' => 'Access denied. User is not an employee.'], 403);
        }

        $employee = $user->employee;
        
        if (!$employee) {
            return response()->json(['error' => 'Employee record not found.'], 404);
        }

        // Fetch user's requests ordered by latest
        $myRequests = EssRequest::where('employee_id', $employee->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $myRequests
        ]);
    }
}
