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
            deleteBase: "{{ url('/competency') }}",
            jobRolesList: "{{ route('job-roles.index') }}",
            jobRolesStore: "{{ route('job-roles.store') }}",
            jobRolesUpdateBase: "{{ url('/job-roles') }}"
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
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Framework Utilization</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-framework-utilization">0%</h3>
                        </div>
                        <div class="w-10 h-10 bg-indigo-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-diagram-project"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Skill Density</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-skill-density">0.0</h3>
                        </div>
                        <div class="w-10 h-10 bg-sky-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Role Alignment Score</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-role-alignment">0%</h3>
                        </div>
                        <div class="w-10 h-10 bg-emerald-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-people-arrows"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Job Roles</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="stat-job-roles">0</h3>
                        </div>
                        <div class="w-10 h-10 bg-fuchsia-500 text-white rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Framework + Mapping Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 pt-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <button type="button" id="tabFramework" class="text-sm font-semibold pb-3 border-b-2 border-blue-600 text-blue-600 cursor-pointer">
                            Core Framework
                        </button>
                        <button type="button" id="tabMapping" class="text-sm font-semibold pb-3 text-slate-500 hover:text-slate-900 cursor-pointer">
                            Competency Mapping
                        </button>
                    </div>
                    <div id="frameworkControls" class="flex flex-wrap gap-2">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="searchInput" placeholder="Search competencies..." class="pl-9 pr-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all w-64 shadow-sm">
                        </div>
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
                    <div id="mappingControls" class="hidden flex flex-wrap gap-2">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="mappingSearchInput" placeholder="Search job roles..." class="pl-9 pr-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all w-64 shadow-sm">
                        </div>
                        <button onclick="openAssignModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                            <i class="fa-solid fa-plus mr-2"></i> Assign
                        </button>
                    </div>
                </div>

                <div id="frameworkPanel">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h2 class="text-lg font-bold text-slate-800 leading-none">Core Competency Framework</h2>
                        <p class="text-sm text-slate-500 mt-1">Manage skill requirements and proficiency expectations</p>
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
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="mappingPanel" class="hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800 leading-none">Competency Mapping</h2>
                            <p class="text-sm text-slate-500 mt-1">Map job roles to assigned competencies and weighting.</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase tracking-widest font-bold">
                                    <th class="px-6 py-4 border-b">Job Role</th>
                                    <th class="px-6 py-4 border-b">Competencies Assigned</th>
                                    <th class="px-6 py-4 border-b">Weightening</th>
                                    <th class="px-6 py-4 border-b">Description</th>
                                    <th class="px-6 py-4 border-b text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="mapping-table-body">
                            </tbody>
                        </table>
                    </div>
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

    <!-- ASSIGN MODAL -->
    <div id="assignModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="assignModalContent" class="modal-content bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Assign Competencies</h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium uppercase tracking-wider">Map Skills to Job Role</p>
                </div>
                <button onclick="closeAssignModal()" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-200/50">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Job Role</label>
                        <select id="assignJobRole" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all bg-white cursor-pointer">
                            <option value="">Select a Job Role...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Calculated Weighting (Real-time)</label>
                        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div id="assignWeightBar" class="h-full bg-blue-500 w-0 transition-all duration-300"></div>
                            </div>
                            <span id="assignWeightLabel" class="text-sm font-bold text-slate-700 w-20 text-right">None</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Based on the average proficiency/weight of selected competencies.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Assign Competencies</label>
                        <div class="relative mb-3">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="assignCompSearch" placeholder="Search competencies..." class="pl-9 pr-4 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all w-full shadow-sm">
                        </div>
                        <div id="assignCompList" class="max-h-60 overflow-y-auto border border-slate-200 rounded-lg p-2 space-y-1 custom-scrollbar">
                            <!-- JS populated -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeAssignModal()" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-800">Cancel</button>
                <button onclick="confirmAction('assign-save')" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                    Save Assignment
                </button>
            </div>
        </div>
    </div>

    <!-- COMPETENCY MAPPING EDIT MODAL -->
    <div id="mappingEditModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="mappingEditModalContent" class="modal-content bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Edit Competency Mapping</h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium uppercase tracking-wider">Job Role Alignment</p>
                </div>
                <button onclick="closeMappingEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-200/50">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1">
                <input type="hidden" id="mappingEditingId">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Job Role</label>
                            <input id="mappingJobRole" type="text" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-600 text-sm" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Weightening</label>
                            <div class="flex items-center gap-3">
                                <input id="mappingWeightSlider" type="range" min="1" max="5" value="3" class="w-full accent-blue-600">
                                <span id="mappingWeightLabel" class="text-xs font-semibold text-slate-600 whitespace-nowrap">Medium</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Description</label>
                            <textarea id="mappingDescription" rows="4" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-600 text-sm" disabled></textarea>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Competencies Search</label>
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input id="mappingCompSearch" type="text" placeholder="Search competencies..." class="pl-9 pr-3 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm w-full">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Competencies Assigned</label>
                            <div id="mappingCompList" class="border border-slate-200 rounded-lg max-h-56 overflow-y-auto custom-scrollbar px-3 py-2 space-y-1 bg-slate-50">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeMappingEditModal()" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-800">Cancel</button>
                <button onclick="confirmAction('mapping-save')" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                    Commit Mapping
                </button>
            </div>
        </div>
    </div>

    <!-- COMPETENCY MAPPING VIEW MODAL -->
    <div id="mappingViewModalOverlay" class="modal-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="mappingViewModalContent" class="modal-content bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden p-8">
            <div id="mappingViewBody"></div>
            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeMappingViewModal()" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-800">Close</button>
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
