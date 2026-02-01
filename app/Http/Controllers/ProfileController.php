<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Models\ActivityLog;
use App\Models\Account;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Eager load employee and its relationships if needed
        $employee = $user->employee; // Relationship is defined in Account model
        
        // Fetch all recent activities
        $activities = ActivityLog::where('user_id', $user->Login_ID)
            ->latest()
            ->take(10)
            ->get();
            
        // Login Frequency (e.g., logins in the last 30 days)
        $loginFrequency = ActivityLog::where('user_id', $user->Login_ID)
            ->where('action', 'Login')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('partials.profile', compact('user', 'employee', 'activities', 'loginFrequency'));
    }

    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Account::find(Auth::id());
        
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $fileContents = file_get_contents($file->getRealPath());
            
            // Save binary data to path_img column
            $user->path_img = $fileContents;
            $user->save();
            
            // Log activity
            ActivityLog::create([
                'user_id' => $user->Login_ID,
                'action' => 'Profile Update',
                'description' => 'Updated profile picture.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return back()->with('success', 'Profile picture updated successfully.');
        }

        return back()->with('error', 'Failed to update profile picture.');
    }

    public function sendPasswordOtp(Request $request)
    {
        $user = Auth::user();
        
        $otp = rand(100000, 999999);
        
        // Store OTP in session
        Session::put('password_change_otp', $otp);
        Session::put('password_change_otp_expires_at', time() + 300); // 5 minutes
        
        // Send Email
        try {
            $html = '
                <div style="font-family: Arial, sans-serif; background-color:#f5f3ff; padding:24px;">
                    <div style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e5e7eb;">
                        <div style="text-align:center;margin-bottom:16px;">
                            <div style="font-size:18px;font-weight:700;color:#5b21b6;">ViaHale</div>
                        </div>
                        <h2 style="font-size:20px;margin-bottom:8px;color:#111827;">Password Change Request</h2>
                        <p style="font-size:14px;color:#4b5563;margin-bottom:16px;">
                            Use the code below to verify your request to change your password.
                        </p>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:12px 24px;border-radius:999px;background:#5b21b6;color:#ffffff;font-size:24px;letter-spacing:0.3em;font-weight:700;">
                                ' . $otp . '
                            </div>
                        </div>
                        <p style="font-size:13px;color:#6b7280;margin-bottom:4px;">
                            This code will expire in <strong>5 minutes</strong>.
                        </p>
                        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">
                            If you did not request this change, please contact support immediately.
                        </p>
                    </div>
                </div>
            ';

            Mail::send([], [], function ($message) use ($user, $html) {
                $message->to($user->email)
                    ->subject('Password Change OTP - ViaHale')
                    ->html($html);
            });
            
            return response()->json(['success' => true, 'message' => 'OTP sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP. ' . $e->getMessage()]);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $otp = $request->otp;
        $sessionOtp = Session::get('password_change_otp');
        $expiresAt = Session::get('password_change_otp_expires_at');

        if (!$sessionOtp || !$expiresAt || time() > $expiresAt) {
            return response()->json(['success' => false, 'message' => 'OTP expired or invalid.']);
        }

        if ($otp != $sessionOtp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.']);
        }

        // Update Password
        $user = Account::find(Auth::id());
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear session
        Session::forget(['password_change_otp', 'password_change_otp_expires_at']);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->Login_ID,
            'action' => 'Password Change',
            'description' => 'User changed their password.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }
}
