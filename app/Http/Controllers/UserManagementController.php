<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index()
    {
        return view('user_management.user-management');
    }

    public function getUsers()
    {
        // User requested to read 'accounts' table
        $users = Account::all();
        // Handle malformed UTF-8 characters by substituting them
        return response()->json($users, 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function getLogs()
    {
        // Return empty array if model/table doesn't exist yet to prevent crash
        try {
            $logs = ActivityLog::latest()->take(50)->get();
            return response()->json($logs, 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
