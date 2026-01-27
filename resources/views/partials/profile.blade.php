<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">

    <!-- Main Container -->
    <div class="profile-container mx-auto px-4 py-8 lg:py-12">
        
        <!-- Profile Header Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
            <div class="h-40 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
            <div class="px-8 pb-8">
                <div class="relative flex flex-col md:flex-row items-end -mt-20 mb-6 gap-6">
                    <div class="relative group">
                        <img id="profilePicMain" src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="Profile" class="w-40 h-40 rounded-3xl border-4 border-white bg-white shadow-xl object-cover transition-transform group-hover:scale-[1.02]">
                        <!-- Change Picture Trigger -->
                        <button onclick="openPictureModal()" class="absolute bottom-3 right-3 p-2.5 bg-indigo-600 text-white rounded-xl shadow-lg hover:bg-indigo-700 transition-all transform hover:scale-110 border-2 border-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </button>
                    </div>
                    <div class="flex-1 pb-2">
                        <h1 class="text-2xl font-bold text-slate-900" id="userName">Alex Rivera</h1>
                        <p class="text-indigo-600 font-medium" id="userPosition">Senior UI/UX Designer</p>
                    </div>
                    <div class="flex gap-3 pb-2">
                        <button onclick="openPasswordModal()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-medium hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Change Password
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-6 border-t border-slate-100">
                    <div>
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">User ID</label>
                        <p class="text-slate-700 font-mono mt-1 font-medium">#USR-99201-AR</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Email Address</label>
                        <p class="text-slate-700 mt-1 font-medium">alex.rivera@company.com</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Department</label>
                        <p class="text-slate-700 mt-1 font-medium">Product & Design</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="space-y-8">
            <!-- Activity Graph -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Performance Metrics</h2>
                        <p class="text-sm text-slate-400 mt-1">Weekly contribution frequency</p>
                    </div>
                    <select class="text-sm border border-slate-200 bg-slate-50 rounded-xl px-4 py-2 text-slate-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                    </select>
                </div>
                <div class="h-72">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="text-xl font-bold text-slate-900 mb-8">Recent Activities</h2>
                <div class="space-y-8">
                    <div class="flex gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div class="flex-1 border-b border-slate-50 pb-6">
                            <p class="text-slate-800 leading-relaxed"><span class="font-semibold text-slate-900">Completed</span> the Q1 Design System Audit and published guidelines.</p>
                            <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                2 hours ago
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <div class="flex-1 border-b border-slate-50 pb-6">
                            <p class="text-slate-800 leading-relaxed"><span class="font-semibold text-slate-900">Commented</span> on "Mobile App Navigation" prototype feedback loop.</p>
                            <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                Yesterday at 4:30 PM
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Picture Modal -->
    <div id="pictureModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="modal-overlay absolute inset-0"></div>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-8 text-center">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-slate-900">Update Profile Picture</h2>
                    <button onclick="closePictureModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                
                <div class="relative inline-block mb-8">
                    <img id="previewImage" src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" class="w-48 h-48 rounded-3xl border-4 border-slate-50 shadow-inner mx-auto object-cover bg-slate-100">
                    <label for="imageUpload" class="absolute -bottom-3 -right-3 p-3 bg-indigo-600 text-white rounded-2xl shadow-xl cursor-pointer hover:bg-indigo-700 transition-all border-4 border-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    </label>
                    <input type="file" id="imageUpload" accept="image/*" class="hidden" onchange="previewFile()">
                </div>

                <p class="text-sm text-slate-500 mb-8">Click the pen icon to select a photo from your device. Recommended size: 500x500px.</p>
                
                <div class="flex gap-3">
                    <button onclick="closePictureModal()" class="flex-1 px-4 py-3 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button onclick="saveNewPicture()" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="modal-overlay absolute inset-0"></div>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Change Password</h2>
                    <button onclick="closePasswordModal()" class="text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                
                <form id="passwordForm" onsubmit="handlePasswordChange(event)" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
                        <input type="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                        <input id="newPassword" type="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                        <input id="confirmPassword" type="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none" placeholder="••••••••">
                        <p id="matchError" class="hidden text-xs text-red-500 mt-1">Passwords do not match</p>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition-all mt-6 shadow-lg shadow-indigo-100">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="modal-overlay absolute inset-0"></div>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm relative z-10 p-8 text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Success!</h2>
            <p id="successText" class="text-slate-500 mb-8">Your profile has been updated successfully.</p>
            <button onclick="closeSuccessModal()" class="w-full bg-slate-900 text-white py-3 rounded-xl font-semibold hover:bg-slate-800 transition-all">
                Got it
            </button>
        </div>
    </div>
</body>
</html>