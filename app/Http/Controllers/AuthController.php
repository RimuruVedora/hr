<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with([
                'login_lockout' => $seconds,
            ])->withErrors(['login_error' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.']);
        }

        $account = Account::where('email', $request->email)->first();

        if (!$account || !Hash::check($request->password, $account->password)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['login_error' => 'Invalid email or password.']);
        }

        RateLimiter::clear($key);

        $otp = rand(100000, 999999);

        $account->auth_code = $otp;
        $account->save();

        Session::regenerate();
        Session::put('auth_otp', $otp);
        Session::put('auth_otp_expires_at', time() + 120);
        Session::put('auth_account_id', $account->getKey());
        Session::put('auth_otp_attempts', 0);
        Session::forget('auth_otp_locked_until');
        Session::save();

        try {
            $html = '
                <div style="font-family: Arial, sans-serif; background-color:#f5f3ff; padding:24px;">
                    <div style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e5e7eb;">
                        <div style="text-align:center;margin-bottom:16px;">
                            <div style="font-size:18px;font-weight:700;color:#5b21b6;">ViaHale</div>
                        </div>
                        <h2 style="font-size:20px;margin-bottom:8px;color:#111827;">Login Verification Code</h2>
                        <p style="font-size:14px;color:#4b5563;margin-bottom:16px;">
                            Use the one-time password (OTP) below to continue logging in to your ViaHale account.
                        </p>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:12px 24px;border-radius:999px;background:#5b21b6;color:#ffffff;font-size:24px;letter-spacing:0.3em;font-weight:700;">
                                ' . $otp . '
                            </div>
                        </div>
                        <p style="font-size:13px;color:#6b7280;margin-bottom:4px;">
                            This code will expire in <strong>2 minutes</strong>.
                        </p>
                        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">
                            If you did not attempt to sign in, you can safely ignore this email.
                        </p>
                        <p style="font-size:13px;color:#9ca3af;margin-top:24px;">
                            — The ViaHale Security Team
                        </p>
                    </div>
                </div>
            ';

            Mail::send([], [], function ($message) use ($account, $html) {
                $message->to($account->email)
                    ->subject('Your Login OTP - ViaHale')
                    ->html($html);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['login_error' => 'Failed to send OTP. Please try again. ' . $e->getMessage()]);
        }

        return back()->with([
            'auth_needed' => true,
            'otp_sent' => true,
            'otp_message' => 'OTP sent to your email.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $otp = $request->otp1 . $request->otp2 . $request->otp3 . $request->otp4 . $request->otp5 . $request->otp6;

        $expiresAt = Session::get('auth_otp_expires_at');
        $accountId = Session::get('auth_account_id');
        $lockedUntil = Session::get('auth_otp_locked_until');
        $account = $accountId ? Account::find($accountId) : null;
        $dbOtp = $account ? $account->auth_code : null;

        if ($lockedUntil && time() < $lockedUntil) {
            $secondsLeft = $lockedUntil - time();

            return back()->with([
                'auth_needed' => true,
                'otp_sent' => true,
            ])->withErrors([
                'otp_error' => 'Too many invalid attempts. Please wait ' . $secondsLeft . ' seconds before trying again.',
            ]);
        }

        if (!$accountId || !$expiresAt || time() > $expiresAt) {
            Session::forget(['auth_otp', 'auth_otp_expires_at', 'auth_account_id', 'auth_otp_attempts', 'auth_otp_locked_until']);

            return back()->withErrors(['login_error' => 'OTP expired. Please login again.']);
        }

        if (!$dbOtp) {
            Session::forget(['auth_otp', 'auth_otp_expires_at', 'auth_account_id', 'auth_otp_attempts', 'auth_otp_locked_until']);

            return back()->withErrors(['login_error' => 'OTP expired. Please login again.']);
        }

        if ($otp != $dbOtp) {
            $attempts = Session::get('auth_otp_attempts', 0) + 1;
            Session::put('auth_otp_attempts', $attempts);

            if ($attempts >= 5) {
                Session::put('auth_otp_locked_until', time() + 10);
                Session::save();

                return back()->with([
                    'auth_needed' => true,
                    'otp_sent' => true,
                ])->withErrors([
                    'otp_error' => 'Too many invalid attempts. Please wait 10 seconds before trying again.',
                ]);
            }

            $remaining = 5 - $attempts;
            Session::save();

            return back()->with([
                'auth_needed' => true,
                'otp_sent' => true,
            ])->withErrors([
                'otp_error' => 'Invalid OTP. You have ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' left.',
            ]);
        }

        Auth::loginUsingId($accountId);
        $account = Auth::user();

        $account->auth_code = null;
        $account->save();

        Session::forget(['auth_otp', 'auth_otp_expires_at', 'auth_account_id', 'auth_otp_attempts', 'auth_otp_locked_until']);

        Session::flash('show_welcome_modal', true);

        // Redirect based on account type
        if ($account->Account_Type == 1) {
            return redirect()->route('admin.dashboard');
        }

        return redirect('/'); // Fallback for other users
    }

    public function resendOtp()
    {
        $accountId = Session::get('auth_account_id');
        if (!$accountId) {
            return response()->json(['success' => false, 'message' => 'Session expired.']);
        }

        $account = Account::find($accountId);
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $otp = rand(100000, 999999);

        $account->auth_code = $otp;
        $account->save();

        Session::put('auth_otp', $otp);
        Session::put('auth_otp_expires_at', time() + 120);
        Session::put('auth_otp_attempts', 0);
        Session::forget('auth_otp_locked_until');
        Session::save();

        try {
            $html = '
                <div style="font-family: Arial, sans-serif; background-color:#f5f3ff; padding:24px;">
                    <div style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e5e7eb;">
                        <div style="text-align:center;margin-bottom:16px;">
                            <div style="font-size:18px;font-weight:700;color:#5b21b6;">ViaHale</div>
                        </div>
                        <h2 style="font-size:20px;margin-bottom:8px;color:#111827;">New Login Verification Code</h2>
                        <p style="font-size:14px;color:#4b5563;margin-bottom:16px;">
                            A new one-time password (OTP) has been generated for your ViaHale login.
                        </p>
                        <div style="text-align:center;margin:24px 0;">
                            <div style="display:inline-block;padding:12px 24px;border-radius:999px;background:#5b21b6;color:#ffffff;font-size:24px;letter-spacing:0.3em;font-weight:700;">
                                ' . $otp . '
                            </div>
                        </div>
                        <p style="font-size:13px;color:#6b7280;margin-bottom:4px;">
                            This code will expire in <strong>2 minutes</strong>.
                        </p>
                        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">
                            If you did not request this code, you can ignore this email.
                        </p>
                        <p style="font-size:13px;color:#9ca3af;margin-top:24px;">
                            — The ViaHale Security Team
                        </p>
                    </div>
                </div>
            ';

            Mail::send([], [], function ($message) use ($account, $html) {
                $message->to($account->email)
                    ->subject('Your Login OTP - ViaHale')
                    ->html($html);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email.']);
        }
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('login');
    }
}
