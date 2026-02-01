<?php

namespace App\Http\Controllers;

use App\Models\EssRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EssRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user is an employee
        if ($user->Account_Type != 2) {
             return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $employee = $user->employee;
        
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found.');
        }

        // Fetch user's requests ordered by latest
        $myRequests = EssRequest::where('employee_id', $employee->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('ess.employee-request', compact('myRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_category' => 'required|string',
            'request_type' => 'required|string',
            // dynamic validation based on type could be added here
        ]);

        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Employee record not found.');
        }

        $essRequest = EssRequest::create([
            'employee_id' => $employee->id,
            'request_category' => $request->request_category,
            'request_type' => $request->request_type,
            'details' => $request->except(['_token', 'request_category', 'request_type', 'attachment']),
            'status' => 'pending',
        ]);

        // Handle file upload if present
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ess_attachments', 'public');
            $essRequest->update(['attachment_path' => $path]);
        }

        return back()->with('success', 'Request submitted successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        $essRequest = EssRequest::where('id', $id)
                        ->where('employee_id', $employee->id)
                        ->firstOrFail();

        // Only allow update if status is returned
        if ($essRequest->status !== 'returned') {
            return back()->with('error', 'You can only edit returned requests.');
        }

        // Update details and set status back to pending
        $essRequest->update([
            'details' => array_merge($essRequest->details ?? [], $request->except(['_token', 'request_category', 'request_type', 'attachment', '_method'])),
            'status' => 'pending',
        ]);
        
         // Handle file upload if present
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ess_attachments', 'public');
            $essRequest->update(['attachment_path' => $path]);
        }

        return back()->with('success', 'Request updated and resubmitted.');
    }

    public function downloadResponse($id)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        $essRequest = EssRequest::where('id', $id)
                        ->where('employee_id', $employee->id)
                        ->firstOrFail();

        if (!$essRequest->response_file_data) {
            return back()->with('error', 'No file available for download.');
        }

        return response($essRequest->response_file_data)
            ->header('Content-Type', $essRequest->response_file_mime ?? 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . ($essRequest->response_file_name ?? 'document.pdf') . '"');
    }
}
