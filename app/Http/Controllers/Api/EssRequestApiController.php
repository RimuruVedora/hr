<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EssRequest;
use Illuminate\Http\Request;

class EssRequestApiController extends Controller
{
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
