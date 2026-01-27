<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competency Performance Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/competency/competency-gap-analytics.css', 'resources/js/competency/competency-gap-analytics.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased custom-scrollbar">

    @include('partials.admin-sidebar')

    <div class="main-content">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        
        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Competency Performance</h1>
                <p class="text-slate-500 mt-1">Monitor KPI alignment, skill gaps, and role requirements across the organization.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search employee..." class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64 shadow-sm">
                </div>
                <div class="flex gap-2">
                    <select id="deptFilter" onchange="filterTable()" class="bg-white border border-slate-200 text-slate-600 px-3 py-2 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <option value="">All Departments</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Management">Management</option>
                        <option value="HR">HR</option>
                        <option value="Security">Security</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Dashboard Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Employees</p>
                        <h3 class="text-3xl font-black text-slate-800 mt-1" id="stat-total-emp">0</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Departments</p>
                        <h3 class="text-3xl font-black text-slate-800 mt-1">4</h3>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-sitemap"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Active Roles</p>
                        <h3 class="text-3xl font-black text-slate-800 mt-1">12</h3>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Employee KPI & Gap Registry</h3>
                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded font-bold uppercase">Live Data</span>
            </div>
            <div class="table-container custom-scrollbar">
                <table class="w-full text-left border-collapse" id="performanceTable">
                    <thead class="sticky top-0 bg-white z-10 shadow-sm">
                        <tr class="text-slate-500 text-[10px] uppercase tracking-widest font-bold">
                            <th class="px-6 py-4 border-b">Employee</th>
                            <th class="px-6 py-4 border-b">Role & Dept</th>
                            <th class="px-6 py-4 border-b">Current vs Required</th>
                            <th class="px-6 py-4 border-b">KPI Status</th>
                            <th class="px-6 py-4 border-b">Priority</th>
                            <th class="px-6 py-4 border-b text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="performance-table-body">
                        <!-- JS populated -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Aggregate Summary Line Graph (Required vs Not Required) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Organizational Competency Hit Rate</h3>
                    <p class="text-sm text-slate-500">Comparison of employees meeting required standards vs. those below target.</p>
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-slate-600">Hit Targets</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="text-xs font-bold text-slate-600">Gaps Identified</span>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="summaryLineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL: Individual Performance Analytics -->
    <div id="viewModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="viewModalContent" class="modal-content bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden p-8 scale-95 opacity-0 transition-all duration-300">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 id="modalEmpName" class="text-2xl font-black text-slate-900 leading-none">Employee Name</h2>
                    <p id="modalEmpRole" class="text-sm text-slate-500 mt-2">Position Detail</p>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 p-2"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <div class="space-y-6">
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Competency Gap Analysis</h4>
                    <div class="h-64">
                        <canvas id="gapChart"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Achieved Levels</p>
                        <p id="modalAchieved" class="text-lg font-black text-emerald-700">0%</p>
                    </div>
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-100">
                        <p class="text-[10px] font-bold text-rose-600 uppercase tracking-widest">Total Gap</p>
                        <p id="modalGap" class="text-lg font-black text-rose-700">0 Points</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                <button onclick="closeModal()" class="bg-slate-900 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-lg shadow-slate-200">Close Analysis</button>
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