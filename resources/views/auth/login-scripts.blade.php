<div id="login-config"
     data-auth-needed="{{ session('auth_needed') ? 'true' : 'false' }}"
     data-otp-sent="{{ session('otp_sent') ? 'true' : 'false' }}"
     data-login-lockout="{{ session('login_lockout') ?? 0 }}"
     data-otp-resend-route="{{ route('otp.resend') }}"
     style="display: none;">
</div>

@vite('resources/js/login/login.js')
