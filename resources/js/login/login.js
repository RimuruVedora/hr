document.addEventListener('DOMContentLoaded', function() {
    // Read configuration from the DOM
    const configEl = document.getElementById('login-config');
    const config = {
        authNeeded: configEl?.dataset.authNeeded === 'true',
        otpSent: configEl?.dataset.otpSent === 'true',
        loginLockout: parseInt(configEl?.dataset.loginLockout || '0'),
        otpResendRoute: configEl?.dataset.otpResendRoute || ''
    };

    // Password Toggle Logic
    const btn = document.getElementById('togglePass');
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeOpen');
    btn?.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      icon.setAttribute('name', show ? 'eye-off-outline' : 'eye-outline');
    });

    // OTP Modal, Inputs, and Countdown Logic
    const otpModalEl = document.getElementById('otpModal');
    if (otpModalEl) {
        const otpModal = new bootstrap.Modal(otpModalEl);
        const otpInputs = document.querySelectorAll('.otp-input');
        const countdownEl = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');
        let timeLeft = 120;
        let timer;

        // Function to show the modal
        function showOTPModal() {
            otpModal.show();
            startCountdown();
        }

        // Function to start the countdown
        function startCountdown() {
            timeLeft = 120;
            clearInterval(timer);
            updateCountdown();
            timer = setInterval(updateCountdown, 1000);
        }

        // Check if OTP modal needs to be shown
        if (config.authNeeded && config.otpSent) {
            showOTPModal();
        }

        // Function to update the countdown timer display
        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            if (countdownEl) {
                countdownEl.textContent = `Resend OTP in ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    countdownEl.classList.add('d-none');
                    resendBtn.classList.remove('d-none');
                } else {
                    timeLeft--;
                }
            }
        }

        // Function to handle resend OTP
        function resendOTP() {
            if (!config.otpResendRoute) return;
            
            fetch(config.otpResendRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('New OTP has been sent to your email');
                    countdownEl.classList.remove('d-none');
                    resendBtn.classList.add('d-none');
                    startCountdown();
                    
                    // Clear OTP fields
                    otpInputs.forEach(input => {
                        input.value = '';
                    });
                    if(otpInputs.length > 0) otpInputs[0].focus();
                } else {
                    alert(data.message || 'Failed to resend OTP');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to resend OTP');
            });
        }
        
        // Attach event listener to resend button
        if (resendBtn) {
            resendBtn.addEventListener('click', (e) => {
                e.preventDefault();
                resendOTP();
            });
        }

        // OTP Input Navigation & Paste Handling
        otpInputs.forEach((input, index) => {
            // Handle input (typing)
            input.addEventListener('input', function() {
                // Move to next field if filled
                if (this.value.length >= this.maxLength) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
                
                // Auto-submit if last field is filled
                if (index === otpInputs.length - 1 && this.value.length === 1) {
                    const form = document.getElementById('otpForm');
                    if (form) form.submit();
                }
            });

            // Handle backspace/delete
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });

        // Handle paste event for OTP
        const otpForm = document.getElementById('otpForm');
        if (otpForm) {
            otpForm.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text/plain').trim();
                if (/^\d{6}$/.test(pasteData)) {
                    for (let i = 0; i < 6; i++) {
                        if (otpInputs[i]) otpInputs[i].value = pasteData[i];
                    }
                    if (otpInputs[5]) otpInputs[5].focus();
                    otpForm.submit();
                }
            });
        }
    }

    // Lockout Modal Logic
    if (config.loginLockout > 0) {
        const lockoutModalEl = document.getElementById('lockoutModal');
        if (lockoutModalEl) {
            const lockoutModal = new bootstrap.Modal(lockoutModalEl);
            const lockoutCountdownEl = document.getElementById('lockoutCountdown');
            const lockoutOkBtn = document.getElementById('lockoutOkBtn');
            let lockoutTimeLeft = config.loginLockout;
            let lockoutTimer;

            lockoutModal.show();
            updateLockoutCountdown();
            lockoutTimer = setInterval(updateLockoutCountdown, 1000);

            function updateLockoutCountdown() {
                const minutes = Math.floor(lockoutTimeLeft / 60);
                const seconds = lockoutTimeLeft % 60;
                if (lockoutCountdownEl) {
                    lockoutCountdownEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }

                if (lockoutTimeLeft <= 0) {
                    clearInterval(lockoutTimer);
                    if (lockoutCountdownEl) lockoutCountdownEl.textContent = "00:00";
                    if (lockoutOkBtn) {
                        lockoutOkBtn.disabled = false;
                        lockoutOkBtn.style.opacity = '1';
                        lockoutOkBtn.style.cursor = 'pointer';
                        lockoutOkBtn.textContent = "Try Again";
                    }
                } else {
                    lockoutTimeLeft--;
                }
            }
        }
    }
});
