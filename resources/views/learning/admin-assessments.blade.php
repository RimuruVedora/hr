<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>Exam Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    @vite(['resources/css/learning/learning_exam_creation.css', 'resources/js/learning/assessment.js'])
    <script>
        window.exams = @json($exams);
        window.courses = @json($courses);
        window.competencies = @json($competencies);
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
       
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    @include('partials.admin-sidebar')

    <div class="main-content">
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto p-4 md:p-12">
        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <i class='bx bxs-graduation text-blue-600 text-3xl'></i>
                    <h1 class="text-3xl font-bold tracking-tight">Exam Dashboard</h1>
                </div>
                <p class="text-slate-500">Manage and track assessment performance across all courses</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400'></i>
                    <input type="text" id="searchInput" oninput="applyFilters()" placeholder="Search by title or course..." class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white w-64 transition-all focus:w-80">
                </div>
                <button onclick="openModal('createModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition shadow-lg shadow-blue-200 whitespace-nowrap">
                    <i class='bx bx-plus-circle'></i> Create Exam
                </button>
            </div>
        </header>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600"><i class='bx bx-file'></i></div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Total</span>
                </div>
                <h3 class="text-2xl font-bold">128</h3>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600"><i class='bx bx-check-shield'></i></div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Active</span>
                </div>
                <h3 class="text-2xl font-bold">42</h3>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600"><i class='bx bx-edit'></i></div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Drafts</span>
                </div>
                <h3 class="text-2xl font-bold">15</h3>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600"><i class='bx bx-book'></i></div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Courses</span>
                </div>
                <h3 class="text-2xl font-bold">24</h3>
            </div>
        </div>

        <!-- Filters & Table Section -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-12">
            <div class="border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between px-6 bg-slate-50/50">
                <div class="flex">
                    <button onclick="switchTab('published')" id="tabBtn-published" class="px-6 py-4 font-semibold text-blue-600 border-b-2 border-blue-600">Published</button>
                    <button onclick="switchTab('drafts')" id="tabBtn-drafts" class="px-6 py-4 font-semibold text-slate-400 hover:text-slate-600">Drafts</button>
                </div>
                <div class="flex flex-wrap items-center gap-3 py-3 md:py-0">
                    <select id="scopeFilter" onchange="applyFilters()" class="text-sm border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="all">All Scopes</option>
                        <option value="internal">Internal</option>
                        <option value="departmental">Departmental</option>
                        <option value="personal">Personal</option>
                    </select>
                    <select id="proficiencyFilter" onchange="applyFilters()" class="text-sm border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="all">All Levels</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                    <button onclick="resetFilters()" class="p-2 text-slate-400 hover:text-rose-500 transition"><i class='bx bx-refresh text-xl'></i></button>
                </div>
            </div>

            <!-- Tables -->
            <div id="tab-published" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-widest">
                            <th class="px-6 py-4 font-semibold">Course</th>
                            <th class="px-6 py-4 font-semibold">Exam Title</th>
                            <th class="px-6 py-4 font-semibold">Scope</th>
                            <th class="px-6 py-4 font-semibold text-center">Proficiency</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-center">Items</th>
                            <th class="px-6 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="publishedTableBody" class="divide-y divide-slate-100 text-sm"></tbody>
                </table>
            </div>

            <div id="tab-drafts" class="hidden overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-widest">
                            <th class="px-6 py-4 font-semibold">Course</th>
                            <th class="px-6 py-4 font-semibold">Exam Title</th>
                            <th class="px-6 py-4 font-semibold">Scope</th>
                            <th class="px-6 py-4 font-semibold text-center">Proficiency</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-center">Items</th>
                            <th class="px-6 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="draftsTableBody" class="divide-y divide-slate-100 text-sm"></tbody>
                </table>
            </div>
            
            <div id="emptyState" class="hidden p-20 text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400 text-4xl"><i class='bx bx-search-alt'></i></div>
                <h3 class="text-lg font-bold text-slate-800">No results found</h3>
            </div>
        </div>
    </main>
    </div>

    <!-- MODAL: Create Exam Wizard -->
    <div id="createModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] shadow-2xl flex flex-col overflow-hidden animate-in slide-in-from-bottom-4 duration-300">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-blue-50/30">
                <h2 class="font-bold text-xl flex items-center gap-2"><i class='bx bx-plus-circle text-blue-600'></i> Create New Exam</h2>
                <button onclick="closeModal('createModal')" class="text-slate-400 hover:text-slate-600 text-2xl"><i class='bx bx-x'></i></button>
            </div>
            
            <div class="flex items-center justify-between px-16 py-6 bg-slate-50 border-b border-slate-100">
                <div class="flex flex-col items-center gap-2">
                    <div id="pill-1" class="step-pill active w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 border-slate-200">1</div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Basic Info</span>
                </div>
                <div class="flex-1 h-1 bg-slate-200 mx-4 -mt-6"></div>
                <div class="flex flex-col items-center gap-2">
                    <div id="pill-2" class="step-pill w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 border-slate-200">2</div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Questions</span>
                </div>
                <div class="flex-1 h-1 bg-slate-200 mx-4 -mt-6"></div>
                <div class="flex flex-col items-center gap-2">
                    <div id="pill-3" class="step-pill w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 border-slate-200">3</div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Review</span>
                </div>
            </div>

            <div class="p-8 overflow-y-auto flex-1 custom-scrollbar bg-white">
                <!-- STEP 1: Basic Information -->
                <div id="step-1" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Step 1: Exam's Basic Information</h3>
                        <p class="text-slate-500 text-sm">Provide core identification and settings for this assessment.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Course</label>
                            <select id="courseSelect" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Exam Title</label>
                            <input type="text" id="examTitle" placeholder="e.g. Finals 2024 - Logic" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Items</label>
                            <input type="number" id="examItems" value="10" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Organizational Scope</label>
                            <select id="examScope" class="w-full p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
                                <option value="public">Public</option>
                                <option value="internal">Internal</option>
                                <option value="departmental">Departmental</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Skills Assessed</label>
                            
                            <!-- Custom Multi-Select Component -->
                            <div class="relative group" id="skillsDropdownContainer">
                                <!-- Selected Skills Tags -->
                                <div id="selectedSkillsContainer" class="flex flex-wrap gap-2 mb-2 empty:hidden"></div>
                                
                                <!-- Search Input -->
                                <div class="relative">
                                    <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400'></i>
                                    <input type="text" id="skillSearchInput" placeholder="Search and select competencies..." 
                                           class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-slate-50/50 transition-all"
                                           autocomplete="off">
                                    <button id="clearSkillsBtn" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 hidden">
                                        <i class='bx bx-x-circle'></i>
                                    </button>
                                </div>

                                <!-- Dropdown List -->
                                <div id="skillsListDropdown" class="hidden absolute z-20 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar animate-in fade-in slide-in-from-top-2 duration-200">
                                    <!-- Items will be injected here via JS -->
                                    <div class="p-4 text-center text-slate-500 text-sm">Start typing to search...</div>
                                </div>
                                
                                <!-- Hidden input for form submission compatibility -->
                                <input type="hidden" id="examSkills" name="skills">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Questions & Points -->
                <div id="step-2" class="hidden space-y-8">
                    <div class="flex justify-between items-end">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Step 2: Add Questions</h3>
                            <p class="text-slate-500 text-sm">Compose your assessment content below.</p>
                        </div>
                        <button class="text-blue-600 font-bold flex items-center gap-1 hover:underline">
                            <i class='bx bx-import'></i> Import CSV
                        </button>
                    </div>

                    <div class="space-y-8" id="questionsContainer">
                        <!-- Individual Question Block -->
                        <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl relative group question-block" data-id="1">
                            <div class="absolute -left-3 top-6 w-8 h-8 bg-white border border-slate-200 rounded-lg flex items-center justify-center font-bold text-blue-600 shadow-sm question-number">1</div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Question Input</label>
                                    <textarea placeholder="Type your question here..." class="question-text w-full p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white min-h-[100px]"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-2 md:col-span-1">
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Attach Picture (Optional)</label>
                                        <div class="relative group">
                                            <input type="file" class="hidden" id="q1-file">
                                            <label for="q1-file" class="file-label flex items-center gap-2 p-3 border-2 border-dashed border-slate-300 rounded-xl bg-white hover:border-blue-500 cursor-pointer transition text-slate-500">
                                                <i class='bx bx-image-add text-xl'></i>
                                                <span class="text-sm">Choose image...</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-span-2 md:col-span-1">
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Points Allocation</label>
                                        <input type="number" value="1" class="question-points w-full p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Answer Options</label>
                                    <div class="space-y-3 options-container" id="q1-options">
                                        <!-- Option Row -->
                                        <div class="flex items-center gap-3 group/row option-row">
                                            <div class="relative">
                                                <input type="checkbox" id="q1-opt1-correct" class="hidden correct-checkbox">
                                                <label for="q1-opt1-correct" title="Mark as correct answer" class="w-10 h-10 flex items-center justify-center border-2 border-slate-200 text-slate-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:text-emerald-500 transition-all">
                                                    <i class='bx bx-check text-2xl'></i>
                                                </label>
                                            </div>
                                            <div class="flex-1 relative">
                                                <input type="text" placeholder="Enter answer text..." class="option-text w-full p-3 pr-10 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white shadow-sm">
                                            </div>
                                            <button onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button onclick="addAnswerOption('q1-options')" class="add-option-btn mt-3 text-sm font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                        <i class='bx bx-plus-circle'></i> Add Option
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button onclick="addQuestion()" class="w-full py-4 border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 font-bold hover:bg-slate-50 hover:border-slate-300 transition flex items-center justify-center gap-2">
                            <i class='bx bx-plus-circle'></i> Add Another Question
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Final Review -->
                <div id="step-3" class="hidden space-y-6">
                    <div class="text-center py-4">
                        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto text-3xl mb-3">
                            <i class='bx bx-paper-plane'></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Final Review</h3>
                        <p class="text-slate-500 text-sm">Please double check all details before submitting.</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Step 1 Summary -->
                        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">1</span>
                                Basic Information
                            </h4>
                            <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm">
                                <div>
                                    <span class="block text-slate-400 text-xs uppercase font-bold">Course</span>
                                    <span id="review-course" class="font-medium text-slate-700"></span>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-xs uppercase font-bold">Exam Title</span>
                                    <span id="review-title" class="font-medium text-slate-700"></span>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-xs uppercase font-bold">Scope</span>
                                    <span id="review-scope" class="font-medium text-slate-700 capitalize"></span>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-xs uppercase font-bold">Skills</span>
                                    <div id="review-skills" class="flex flex-wrap gap-1 mt-1"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 Summary -->
                        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                            <h4 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">2</span>
                                Questions Summary
                            </h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm border-b border-slate-200 pb-2">
                                    <span class="text-slate-500">Total Questions</span>
                                    <span id="review-total-questions" class="font-bold text-slate-700"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm border-b border-slate-200 pb-2">
                                    <span class="text-slate-500">Total Points</span>
                                    <span id="review-total-points" class="font-bold text-slate-700"></span>
                                </div>
                                
                                <div class="mt-4">
                                    <span class="block text-slate-400 text-xs uppercase font-bold mb-2">Questions Preview</span>
                                    <div id="review-questions-list" class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                                        <!-- JS will populate this -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 border-t border-slate-100 bg-slate-50 flex justify-between">
                <button id="prevBtn" onclick="navigateStep(-1)" class="invisible px-6 py-2.5 border border-slate-200 bg-white rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition">Back</button>
                <button id="nextBtn" onclick="navigateStep(1)" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">Next Step</button>
                <button id="finishBtn" onclick="openModal('confirmModal')" class="hidden bg-emerald-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-100">Submit for Approval</button>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL: Published (Read-only) -->
    <div id="viewModalPublished" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden animate-in zoom-in-95">
            <div class="px-6 py-4 border-b bg-slate-50 flex justify-between items-center">
                <h2 class="font-bold text-xl flex items-center gap-2"><i class='bx bx-show text-blue-600'></i> View Exam Details</h2>
                <button onclick="closeModal('viewModalPublished')" class="text-slate-400 hover:text-slate-600"><i class='bx bx-x text-2xl'></i></button>
            </div>
            <div class="p-8 grid grid-cols-2 gap-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Course</label><p id="view-course" class="font-bold text-slate-800"></p></div>
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Exam Title</label><p id="view-title" class="font-bold text-slate-800"></p></div>
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Items</label><p id="view-items" class="font-bold text-slate-800"></p></div>
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Scope</label><p id="view-scope" class="font-bold text-slate-800 capitalize"></p></div>
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Proficiency</label><p id="view-proficiency" class="font-bold text-slate-800 capitalize"></p></div>
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Status</label><span id="view-status" class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded uppercase">Published</span></div>
                <div class="col-span-2"><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Skills Assessed</label><div id="view-skills" class="flex flex-wrap gap-2 mt-1"></div></div>
                <div class="col-span-2"><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Description</label><p id="view-description" class="text-slate-600 text-sm italic"></p></div>
                <div class="col-span-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Questions</label>
                    <div id="view-questions-list" class="space-y-4 mt-2 max-h-60 overflow-y-auto custom-scrollbar p-1"></div>
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t flex justify-end"><button onclick="closeModal('viewModalPublished')" class="px-6 py-2 border rounded-xl font-bold bg-white">Close</button></div>
        </div>
    </div>

    <!-- VIEW MODAL: Draft -->
    <div id="viewModalDraft" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden animate-in zoom-in-95">
            <div class="px-6 py-4 border-b bg-amber-50 flex justify-between items-center">
                <h2 class="font-bold text-xl flex items-center gap-2"><i class='bx bx-edit-alt text-amber-600'></i> Review Draft Exam</h2>
                <button onclick="closeModal('viewModalDraft')" class="text-slate-400 hover:text-slate-600"><i class='bx bx-x text-2xl'></i></button>
            </div>
            <div class="p-8 grid grid-cols-2 gap-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Course</label><p id="draft-view-course" class="font-bold text-slate-800"></p></div>
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Exam Title</label><p id="draft-view-title" class="font-bold text-slate-800"></p></div>
                <div><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Status</label><span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-1 rounded uppercase">Draft Assessment</span></div>
                <div class="col-span-2"><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Skills</label><div id="draft-view-skills" class="flex flex-wrap gap-2 mt-1"></div></div>
                <div class="col-span-2"><label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Description</label><p id="draft-view-description" class="text-slate-600 text-sm italic"></p></div>
                <div class="col-span-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Questions</label>
                    <div id="draft-view-questions-list" class="space-y-4 mt-2 max-h-60 overflow-y-auto custom-scrollbar p-1"></div>
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t flex justify-between items-center">
                <button onclick="openModal('confirmModal')" class="px-6 py-2.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl font-bold hover:bg-rose-100">Return to Editor</button>
                <div class="flex gap-3">
                    <button onclick="closeModal('viewModalDraft')" class="px-6 py-2.5 bg-white border border-slate-200 rounded-xl font-bold">Cancel</button>
                    <button onclick="openModal('confirmModal')" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700">Approve & Publish</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Confirmation -->
    <div id="confirmModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-8 text-center animate-in zoom-in-95">
            <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"><i class='bx bx-help-circle'></i></div>
            <h3 class="text-xl font-bold mb-2">Are you sure?</h3>
            <p class="text-slate-500 text-sm">Do you want to confirm this action?</p>
            <div class="grid grid-cols-2 gap-3 mt-8">
                <button onclick="closeModal('confirmModal')" class="py-3 border border-slate-200 rounded-xl font-bold">No, Cancel</button>
                <button onclick="submitExam()" class="py-3 bg-blue-600 text-white rounded-xl font-bold">Yes, Confirm</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Success -->
    <div id="successModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-10 text-center">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl"><i class='bx bx-check-double'></i></div>
            <h3 class="text-2xl font-bold mb-2">Successful!</h3>
            <button onclick="location.reload()" class="w-full py-4 bg-slate-900 text-white rounded-xl mt-6">Return Home</button>
        </div>
    </div>

</body>
</html>