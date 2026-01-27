<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competency Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        window.COMPETENCY_ENDPOINTS = {
            list: "{{ route('competency.list') }}",
            store: "{{ route('competency.store') }}",
            updateBase: "{{ url('/competency') }}",
            deleteBase: "{{ url('/competency') }}"
        };
    </script>
    @vite(['resources/css/competency/competency_main_dashboard.css','resources/js/competency/competency_main_dashboard.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased custom-scrollbar">
    @include('partials.admin-sidebar')

    <!-- Main Container -->
    <div class="main-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        
        <!-- Page Title & Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Competency Management</h1>
                <p class="text-slate-500 mt-1">Enterprise-wide talent framework and skill-gap intelligence.</p>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="space-y-8">
            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Competencies</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-total">0</h3>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Org-Wide Skills</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-org">0</h3>
                        </div>
                        <div class="w-10 h-10 bg-emerald-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Critical Gaps</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-gaps">0</h3>
                        </div>
                        <div class="w-10 h-10 bg-rose-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Avg. Proficiency</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-avg">0%</h3>
                        </div>
                        <div class="w-10 h-10 bg-amber-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-award"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Framework Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 leading-none">Core Competency Framework</h2>
                        <p class="text-sm text-slate-500 mt-1">Manage skill requirements and proficiency expectations</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <!-- Search Bar -->
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="searchInput" placeholder="Search competencies..." class="pl-9 pr-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all w-64 shadow-sm">
                        </div>

                        <!-- Filter Dropdown -->
                        <select id="filterCategory" class="px-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white text-slate-600 cursor-pointer shadow-sm">
                            <option value="">All Categories</option>
                            <option value="Technical">Technical</option>
                            <option value="Leadership">Leadership</option>
                            <option value="Behavioral">Behavioral</option>
                            <option value="Compliance">Compliance</option>
                        </select>

                        <button onclick="openFormModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-plus mr-2"></i> New Competency
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase tracking-widest font-bold">
                                <th class="px-6 py-4 border-b">Competency Detail</th>
                                <th class="px-6 py-4 border-b">Category</th>
                                <th class="px-6 py-4 border-b">Scope</th>
                                <th class="px-6 py-4 border-b">Proficiency</th>
                                <th class="px-6 py-4 border-b">Status</th>
                                <th class="px-6 py-4 border-b text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="competency-table-body">
                            <!-- JS populated -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN FORM MODAL -->
    <div id="formModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="formModalContent" class="modal-content bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 id="modalTitle" class="text-xl font-bold text-slate-900">Define New Competency</h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium uppercase tracking-wider">System Operational Input</p>
                </div>
                <button onclick="closeFormModal()" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-200/50">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1">
                <input type="hidden" id="editingId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Competency Title</label>
                            <input id="newCompTitle" type="text" placeholder="e.g., Strategic Negotiation" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Category</label>
                            <select id="newCompCategory" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white">
                                <option>Technical</option>
                                <option>Leadership</option>
                                <option>Behavioral</option>
                                <option>Compliance</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Organizational Scope</label>
                            <select id="newCompScope" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white">
                                <option>Team</option>
                                <option>Department</option>
                                <option>Management</option>
                                <option>Organization-wide</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Proficiency Target</label>
                            <select id="newCompProficiency" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white">
                                <option>Beginner</option>
                                <option>Intermediate</option>
                                <option>Advanced</option>
                                <option>Expert</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Priority Weighting</label>
                            <select id="newCompWeight" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Status</label>
                            <select id="newCompStatus" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white">
                                <option value="Active">Active</option>
                                <option value="Review">Review</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Description</label>
                    <textarea id="newCompDesc" rows="3" placeholder="Detail specific skill requirements..." class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all"></textarea>
                </div>
            </div>

            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeFormModal()" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-800">Cancel</button>
                <button onclick="confirmAction('save')" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                    Commit Changes
                </button>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div id="viewModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="viewModalContent" class="modal-content bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden p-8">
            <div id="viewBody"></div>
            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                <button onclick="closeViewModal()" class="bg-slate-900 text-white px-6 py-2 rounded-lg text-sm font-bold">Close Details</button>
            </div>
        </div>
    </div>

    <!-- CONFIRMATION & SUCCESS MODALS -->
    <div id="confirmModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[60] hidden flex items-center justify-center p-4">
        <div id="confirmModalContent" class="modal-content bg-white w-full max-w-sm rounded-2xl shadow-2xl p-8 text-center">
            <div id="confirmIcon" class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl"></div>
            <h3 id="confirmTitle" class="text-xl font-bold text-slate-900 mb-2"></h3>
            <p id="confirmText" class="text-slate-500 text-sm mb-8"></p>
            <div class="flex gap-3">
                <button onclick="closeConfirm()" class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl">Cancel</button>
                <button id="confirmBtn" class="flex-1 px-4 py-2.5 text-sm font-bold text-white rounded-xl">Proceed</button>
            </div>
        </div>
    </div>

    <div id="successModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/40 z-[70] hidden flex items-center justify-center p-4">
        <div id="successModalContent" class="modal-content bg-white w-full max-w-xs rounded-2xl shadow-2xl p-6 text-center">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 id="successTitle" class="text-lg font-bold text-slate-900 mb-1">Success!</h3>
            <p id="successText" class="text-slate-500 text-xs"></p>
        </div>
    </div>

  
</body>
</html>
