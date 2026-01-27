<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        window.APP_ROUTES = {
            users: "{{ route('api.users') }}",
            logs: "{{ route('api.logs') }}"
        };
    </script>
    @vite(['resources/css/user_management/user_management.css', 'resources/js/user_management/user_management.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased custom-scrollbar">

    @include('partials.admin-sidebar')

    <div class="main-content">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            
            <!-- Header -->
            <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">System Administration</h1>
            <p class="text-slate-500 mt-1">Manage user access levels and monitor authentication activity logs.</p>
        </div>

        <!-- Dashboard Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Users</p>
                        <h3 class="text-3xl font-black text-slate-800 mt-1" id="count-total">0</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Active</p>
                        <h3 class="text-3xl font-black text-emerald-600 mt-1" id="count-active">0</h3>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>

            <!-- Inactive Users -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Inactive</p>
                        <h3 class="text-3xl font-black text-rose-600 mt-1" id="count-inactive">0</h3>
                    </div>
                    <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex border-b border-slate-200 mb-6">
            <button onclick="switchTab('users')" id="tab-users" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-blue-600 transition-all tab-active">
                <i class="fa-solid fa-user-group mr-2"></i> User Directory
            </button>
            <button onclick="switchTab('logs')" id="tab-logs" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-blue-600 transition-all">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Activity Logs
            </button>
        </div>

        <!-- Tab 1: Users Table -->
        <div id="view-users" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase tracking-widest font-bold">
                            <th class="px-6 py-4 border-b">User ID</th>
                            <th class="px-6 py-4 border-b">Employee Name</th>
                            <th class="px-6 py-4 border-b">Position</th>
                            <th class="px-6 py-4 border-b">Status</th>
                            <th class="px-6 py-4 border-b text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="user-table-body">
                        <!-- JS populated -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Logs Table -->
        <div id="view-logs" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase tracking-widest font-bold">
                            <th class="px-6 py-4 border-b">Timestamp</th>
                            <th class="px-6 py-4 border-b">Employee</th>
                            <th class="px-6 py-4 border-b">Action</th>
                            <th class="px-6 py-4 border-b">Device/IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="log-table-body">
                        <!-- JS populated -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- EDIT STATUS MODAL -->
    <div id="editModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="editModalContent" class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden p-6 scale-95 opacity-0 transition-all duration-300">
            <div class="text-center">
                <div id="modal-status-icon" class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Update User Status</h3>
                <p id="modal-user-name" class="text-slate-500 text-sm mt-1 mb-6 font-medium"></p>
                
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-6">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 text-left">Set New Status</label>
                    <select id="statusSelect" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white font-bold">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button onclick="closeModal()" class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
                    <button onclick="saveStatus()" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-100 transition-all">Apply Change</button>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <!-- Ion Icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>