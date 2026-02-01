<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile - ViaHale</title>
    <!-- Removed Tailwind CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/dashboard/dashboard.css'])
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen">
    
    @if(auth()->user()->Account_Type == 1)
        @include('partials.admin-sidebar')
    @else
        @include('partials.Employee-sidebar')
    @endif

    <div class="main-content">
        <main class="p-6 lg:p-12 max-w-[1600px] mx-auto">
            
            <div class="mb-10">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">My Profile</h1>
                <p class="text-slate-500 mt-2 font-medium text-lg">Manage your account settings and view activity.</p>
                
                @if(session('success'))
                    <div class="mt-4 p-4 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mt-4 p-4 bg-red-50 text-red-600 rounded-xl border border-red-100 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mt-4 p-4 bg-red-50 text-red-600 rounded-xl border border-red-100">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Profile Card & Login Stats -->
                <div class="space-y-8">
                    <!-- Profile Card -->
                    <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm text-center relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
                        
                        <div class="relative z-10 mt-12">
                            <div class="w-32 h-32 mx-auto rounded-full border-4 border-white shadow-lg bg-white overflow-hidden flex items-center justify-center text-4xl font-bold text-slate-300 relative">
                                @if($user->path_img)
                                    <img src="data:image/jpeg;base64,{{ base64_encode($user->path_img) }}" class="w-full h-full object-cover">
                                @elseif(isset($employee) && $employee)
                                    <!-- Use initials or placeholder if no image -->
                                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                @else
                                    <i class="fas fa-user"></i>
                                @endif

                                <!-- Upload Overlay -->
                                <form action="{{ route('profile.update-picture') }}" method="POST" enctype="multipart/form-data" id="profile-pic-form" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center cursor-pointer">
                                    @csrf
                                    <label for="profile_picture" class="cursor-pointer text-white text-sm font-bold flex flex-col items-center">
                                        <i class="fas fa-camera mb-1"></i>
                                        <span>Change</span>
                                    </label>
                                    <input type="file" name="profile_picture" id="profile_picture" class="hidden" onchange="document.getElementById('profile-pic-form').submit()">
                                </form>
                            </div>
                            
                            <h2 class="mt-4 text-2xl font-black text-slate-900">
                                @if(isset($employee) && $employee)
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                @else
                                    Admin User
                                @endif
                            </h2>
                            <p class="text-slate-500 font-medium">
                                @if(isset($employee) && $employee)
                                    {{ $employee->department ?? 'No Department' }} • {{ $employee->jobRole->name ?? 'No Role' }}
                                @else
                                    Administrator
                                @endif
                            </p>
                            <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-sm font-bold">
                                {{ $user->email }}
                            </div>
                        </div>
                    </div>

                    <!-- Login Frequency -->
                    <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600">
                                <i class="fas fa-chart-line text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900">Login Frequency</h3>
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Last 30 Days</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="text-5xl font-black text-slate-900">{{ $loginFrequency }}</span>
                            <span class="text-slate-500 font-medium ml-2">logins</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Activity Log & Password Change -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Change Password Section -->
                    <div x-data="passwordChange()" class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="p-3 rounded-2xl bg-amber-50 text-amber-600">
                                <i class="fas fa-lock text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900">Security Settings</h3>
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Change Password</p>
                            </div>
                        </div>

                        <!-- Step 1: Initial State -->
                        <div x-show="step === 1">
                            <p class="text-slate-600 mb-6">To change your password, we need to verify your identity. Click the button below to receive a One-Time Password (OTP) via email.</p>
                            <button @click="sendOtp" :disabled="isLoading" class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-all disabled:opacity-50">
                                <span x-show="!isLoading">Request OTP</span>
                                <span x-show="isLoading"><i class="fas fa-spinner fa-spin mr-2"></i>Sending...</span>
                            </button>
                        </div>

                        <!-- Step 2: OTP & New Password Input -->
                        <div x-show="step === 2" x-cloak>
                            <div class="space-y-4 max-w-md">
                                <div class="p-4 bg-blue-50 text-blue-700 rounded-xl text-sm mb-4">
                                    <i class="fas fa-info-circle mr-2"></i> An OTP has been sent to your email.
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Enter OTP</label>
                                    <input type="text" x-model="otp" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all font-mono text-center tracking-widest text-lg" placeholder="000000" maxlength="6">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">New Password</label>
                                    <input type="password" x-model="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                                    <p class="text-xs text-slate-500 mt-1"><i class="fas fa-info-circle mr-1"></i> Password must be at least 8 characters.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Confirm New Password</label>
                                    <input type="password" x-model="password_confirmation" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                                </div>

                                <div class="flex space-x-3 pt-4">
                                    <button @click="confirmAction" :disabled="isSubmitting" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all disabled:opacity-50">
                                        <span x-show="!isSubmitting">Change Password</span>
                                        <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin mr-2"></i>Updating...</span>
                                    </button>
                                    <button @click="step = 1" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition-all">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Modal -->
                        <div x-show="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                            <div class="bg-white rounded-[32px] p-8 max-w-sm w-full mx-4 shadow-2xl transform transition-all">
                                <div class="text-center mb-6">
                                    <div class="w-16 h-16 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 mb-2">Confirm Change</h3>
                                    <p class="text-slate-500">Are you sure you want to update your password?</p>
                                </div>
                                <div class="flex space-x-3">
                                    <button @click="showConfirmModal = false" class="flex-1 px-4 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-colors">Cancel</button>
                                    <button @click="updatePassword" class="flex-1 px-4 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">Yes, Update</button>
                                </div>
                            </div>
                        </div>

                        <!-- Success Modal -->
                        <div x-show="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                            <div class="bg-white rounded-[32px] p-8 max-w-sm w-full mx-4 shadow-2xl transform transition-all text-center">
                                <div class="w-16 h-16 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                                    <i class="fas fa-check"></i>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 mb-2">Success!</h3>
                                <p class="text-slate-500 mb-8">Your password has been changed successfully.</p>
                                <button @click="closeSuccessModal" class="w-full px-6 py-3 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-200">
                                    Continue
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Log -->
                    <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                        <div class="flex items-center space-x-4 mb-8">
                            <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600">
                                <i class="fas fa-history text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900">Recent Activity</h3>
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Account History</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="pb-4 text-xs font-black text-slate-400 uppercase tracking-widest">Date & Time</th>
                                        <th class="pb-4 text-xs font-black text-slate-400 uppercase tracking-widest">Action</th>
                                        <th class="pb-4 text-xs font-black text-slate-400 uppercase tracking-widest">Details</th>
                                        <th class="pb-4 text-xs font-black text-slate-400 uppercase tracking-widest">IP</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    @forelse($activities as $activity)
                                        <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
                                            <td class="py-4 text-slate-600 font-medium whitespace-nowrap">
                                                {{ $activity->created_at->format('M d, Y h:i A') }}
                                            </td>
                                            <td class="py-4 text-slate-800 font-bold">
                                                {{ $activity->action }}
                                            </td>
                                            <td class="py-4 text-slate-500">
                                                {{ $activity->description }}
                                            </td>
                                            <td class="py-4 text-slate-400 font-mono text-xs">
                                                {{ $activity->ip_address }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-slate-400 italic">No recent activity found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        function passwordChange() {
            return {
                step: 1,
                isLoading: false,
                isSubmitting: false,
                showConfirmModal: false,
                showSuccessModal: false,
                otp: '',
                password: '',
                password_confirmation: '',

                async sendOtp() {
                    this.isLoading = true;
                    try {
                        const response = await fetch('{{ route("profile.send-otp") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.step = 2;
                        } else {
                            alert(data.message || 'Failed to send OTP');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                confirmAction() {
                    if (this.password !== this.password_confirmation) {
                        alert('Passwords do not match.');
                        return;
                    }
                    if (this.password.length < 8) {
                        alert('Password must be at least 8 characters.');
                        return;
                    }
                    this.showConfirmModal = true;
                },

                closeSuccessModal() {
                    this.showSuccessModal = false;
                    this.step = 1;
                    this.otp = '';
                    this.password = '';
                    this.password_confirmation = '';
                },

                async updatePassword() {
                    this.showConfirmModal = false;
                    this.isSubmitting = true;
                    try {
                        const response = await fetch('{{ route("profile.update-password") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                otp: this.otp,
                                password: this.password,
                                password_confirmation: this.password_confirmation
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showSuccessModal = true;
                        } else {
                            alert(data.message || 'Failed to update password');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
