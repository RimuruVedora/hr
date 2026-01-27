<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Log in | ViaHale</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  @vite('resources/css/login/login.css')
</head>
<body>
  <header class="brandbar">
    <div class="brand">ViaHale</div>
  </header>


  <div class="wrap">

    <div class="login-card">
      
      @if($errors->has('login_error'))
        <div class="alert alert-danger mb-3">
          {{ $errors->first('login_error') }}
        </div>
      @endif
      
      @if(session('otp_message'))
        <div class="alert alert-success mb-3" style="background-color:rgba(0, 255, 0, 0.15); border:none; color:#fff; font-size:.85rem; border-radius:.5rem;">
          {{ session('otp_message') }}
        </div>
      @endif
      
      <!-- Email/Password Login Form -->
      <form method="post" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email" required autocomplete="email" value="{{ old('email') }}">
          @error('email')
            <p class="text-danger" style="font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
          @enderror
        </div>
        <div class="mb-3 input-icon">
          <label class="form-label">Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
          <button type="button" id="togglePass" aria-label="Show/Hide password">
            <ion-icon name="eye-outline" id="eyeOpen"></ion-icon>
          </button>
          @error('password')
            <p class="text-danger" style="font-size:.8rem; margin-top:.25rem;">{{ $message }}</p>
          @enderror
        </div>
        <button class="btn-login" type="submit" name="submit" >LOGIN <span class="ms-1">►</span></button>
      </form>
    </div>
  </div>


   <!-- Footer -->
  <div class="footer-bar">
    <div>BCP Capstone &nbsp; | &nbsp; 
      <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
    </div>
    <div><a href="#">Need Help?</a></div>
  </div>


<!-- Privacy Notice Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <!-- Header with same login color -->
      <div class="modal-header" style="background-color:#1a1a2e; color:#fff;">
        <h5 class="modal-title" id="privacyModalLabel">Privacy Notice</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>


      <div class="modal-body">
        
        <h6><strong>Effective: September 2025</strong></h6>


        <h6 class="mt-3">Scope of this Notice</h6>
        <p>
          This Privacy Notice ("Notice") explains how <strong>ViaHale</strong> ("we", "our", "us") collects, uses,
          protects, and manages personal data across our integrated systems:
        </p>
        <ul>
          <li><strong>Facilities Reservation System</strong> – for scheduling and managing facility use.</li>
          <li><strong>Visitor Management System</strong> – for visitor registration, verification, and access control.</li>
          <li><strong>Legal Management System</strong> – for handling legal requests, case files, and document tracking.</li>
          <li><strong>Document Management System</strong> – for storing, reviewing, approving, and archiving documents.</li>
        </ul>
        <p>
          This Notice applies to all individuals ("you") whose data is entered into, processed by, or generated through these systems.
          By using our services, you consent to the practices described below.
        </p>


        <h6 class="mt-3">1. Sources of Personal Data</h6>
        <p>We collect personal data from multiple sources, including:</p>
        <ul>
          <li>Online registration forms, booking requests, or document uploads.</li>
          <li>Onsite records such as visitor logbooks and facility sign-in sheets.</li>
          <li>Official identification documents and supporting paperwork.</li>
          <li>System activity logs, login attempts, and access records.</li>
          <li>Email, SMS, or call communications for scheduling and approvals.</li>
          <li>Security tools like CCTV footage, QR code scans, or biometrics (where used).</li>
        </ul>


        <h6 class="mt-3">2. Personal Data We Collect</h6>
        <p>Depending on the module, the data we collect may include:</p>
        <ul>
          <li><strong>Identification</strong>: full name, employee/visitor ID, company, valid ID details, vehicle plate number.</li>
          <li><strong>Contact Information</strong>: email address, phone number, and emergency contacts.</li>
          <li><strong>Reservations & Access</strong>: facility bookings, visit schedules, host/point of contact, purpose of visit.</li>
          <li><strong>Legal & Document Data</strong>: submitted contracts, case files, supporting documents, approvals, and comments.</li>
          <li><strong>Biometric & Security</strong>: photographs, signatures, and biometric identifiers (with explicit consent).</li>
          <li><strong>System & Technical</strong>: usernames, passwords (encrypted), user roles, activity logs, IP address, device/browser info.</li>
        </ul>


        <h6 class="mt-3">3. Personal Data of Minors</h6>
        <p>
          We do not knowingly collect information from individuals below 18 years old without verified parental or guardian consent.
          Any records found without proper consent will be deleted unless required for compliance.
        </p>


        <h6 class="mt-3">4. Cookies, Logs & System Data</h6>
        <p>
          When using our systems online, cookies and tracking logs may collect technical information such as IP address, device details,
          and browsing activity. This helps us maintain security, improve performance, and troubleshoot issues. You may adjust your browser
          to block cookies, though certain features may not function properly.
        </p>


        <h6 class="mt-3">5. Purposes of Processing</h6>
        <p>We process your data for the following legitimate purposes:</p>
        <ul>
          <li><strong>Facilities Reservation</strong>: to confirm bookings, manage schedules, and generate usage reports.</li>
          <li><strong>Visitor Management</strong>: to verify identity, issue passes, and maintain accurate visitor logs.</li>
          <li><strong>Legal Management</strong>: to support legal processes, document reviews, and ensure compliance with policies.</li>
          <li><strong>Document Management</strong>: to organize, approve, and archive organizational records.</li>
          <li><strong>General Operations</strong>: for fraud prevention, audits, analytics, IT security, and compliance with applicable laws.</li>
        </ul>


        <h6 class="mt-3">6. Disclosure of Data</h6>
        <p>
          We may share personal data with authorized staff, IT service providers, security officers, or legal authorities where required.
          Data will only be disclosed on a need-to-know basis and never sold to third parties.
        </p>


        <h6 class="mt-3">7. Retention</h6>
        <p>
          Data is stored only as long as necessary for its intended purpose or as required by law. Once no longer needed,
          data is securely deleted, anonymized, or archived according to retention policies.
        </p>


        <h6 class="mt-3">8. Data Security</h6>
        <p>
          We protect data with technical, organizational, and administrative safeguards including:
        </p>
        <ul>
          <li>Role-based access control and secure authentication.</li>
          <li>Encryption of sensitive information (e.g., passwords, documents).</li>
          <li>Audit logs to monitor system usage and detect suspicious activity.</li>
          <li>Regular backups and secure servers with updated security protocols.</li>
        </ul>
        <p>
          While we strive to safeguard your data, no system is completely secure. Users are advised to keep their
          passwords private and report any suspicious activities immediately.
        </p>


        <h6 class="mt-3">9. Your Rights</h6>
        <p>You have the right to:</p>
        <ul>
          <li>Access and request a copy of your data.</li>
          <li>Request corrections for inaccurate or outdated information.</li>
          <li>Request deletion or restriction of processing when applicable.</li>
          <li>Withdraw consent for optional processing (e.g., biometrics).</li>
          <li>Object to data processing where permitted by law.</li>
          <li>File complaints with the National Privacy Commission (NPC).</li>
        </ul>


        <h6 class="mt-3">10. Changes to this Notice</h6>
        <p>
          We may update this Notice to reflect changes in policies, technology, or legal requirements.
          The latest version will always be posted in the system.
        </p>


        <h6 class="mt-3">11. Contact</h6>
        <p>
          For data privacy concerns, please contact our Data Protection Officer:<br>
          📧 <a href="mailto:privacy@viahale.com">privacy@viahale.com</a><br>
          📞 for demo only
        </p>


      </div>


      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- OTP Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: linear-gradient(180deg,var(--vh-purple-3),var(--vh-purple-2) 55%,var(--vh-purple) 100%); color: #fff; border: none;">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="otpModalLabel">OTP Verification</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-center mb-4">Enter the 6-digit code sent to your email address</p>
        
        <form id="otpForm" action="{{ route('otp.verify') }}" method="POST">
          @csrf
          <div class="d-flex justify-content-between mb-4 gap-2">
            <input type="text" name="otp1" maxlength="1" class="form-control text-center otp-input" style="width: 50px; height: 50px; font-size: 1.5rem; background-color:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25);" autocomplete="off" required inputmode="numeric" pattern="[0-9]*">
            <input type="text" name="otp2" maxlength="1" class="form-control text-center otp-input" style="width: 50px; height: 50px; font-size: 1.5rem; background-color:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25);" autocomplete="off" required inputmode="numeric" pattern="[0-9]*">
            <input type="text" name="otp3" maxlength="1" class="form-control text-center otp-input" style="width: 50px; height: 50px; font-size: 1.5rem; background-color:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25);" autocomplete="off" required inputmode="numeric" pattern="[0-9]*">
            <input type="text" name="otp4" maxlength="1" class="form-control text-center otp-input" style="width: 50px; height: 50px; font-size: 1.5rem; background-color:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25);" autocomplete="off" required inputmode="numeric" pattern="[0-9]*">
            <input type="text" name="otp5" maxlength="1" class="form-control text-center otp-input" style="width: 50px; height: 50px; font-size: 1.5rem; background-color:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25);" autocomplete="off" required inputmode="numeric" pattern="[0-9]*">
            <input type="text" name="otp6" maxlength="1" class="form-control text-center otp-input" style="width: 50px; height: 50px; font-size: 1.5rem; background-color:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25);" autocomplete="off" required inputmode="numeric" pattern="[0-9]*">
          </div>
          
          <div class="text-center mb-4">
            <p id="countdown" class="text-white-50">Resend OTP in 02:00</p>
            <button id="resendBtn" type="button" class="btn btn-link text-white d-none">
              Resend
            </button>
          </div>
          
          <button type="submit" class="btn btn-light w-100" style="color: var(--vh-purple-2); font-weight: 600;">Verify</button>
        </form>
      </div>
    </div>
  </div>
</div>


  <div class="modal fade" id="lockoutModal" tabindex="-1" aria-labelledby="lockoutModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background: linear-gradient(180deg,var(--vh-purple-3),var(--vh-purple-2) 55%,var(--vh-purple) 100%); color: #fff; border: none;">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="lockoutModalLabel">Login Locked</h5>
        </div>
        <div class="modal-body text-center">
          <div class="mb-4">
            <ion-icon name="alert-circle-outline" style="font-size: 4rem; color: #ffcccb;"></ion-icon>
          </div>
          <h5 class="mb-3">Too Many Attempts</h5>
          <p class="mb-4">You have exceeded the maximum number of login attempts. Please wait:</p>
          <div id="lockoutCountdown" style="font-size: 2rem; font-weight: 700; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 10px; display: inline-block;">
            00:00
          </div>
          <p class="mt-4 text-white-50" style="font-size: 0.9rem;">
             Your account is temporarily locked for security.
          </p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-light w-100" id="lockoutOkBtn" data-bs-dismiss="modal" disabled style="color: var(--vh-purple-2); font-weight: 600; opacity: 0.6; cursor: not-allowed;">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


  @include('auth.login-scripts')
</body>
</html>
