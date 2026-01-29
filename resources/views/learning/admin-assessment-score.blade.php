<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Scores & Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [x-cloak] { display: none !important; }
    </style>
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    @include('partials.admin-sidebar')

    <div class="main-content ml-0 md:ml-64 transition-all duration-300">
        <main class="max-w-7xl mx-auto p-4 md:p-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                        <i class='bx bx-bar-chart-alt-2 text-blue-600'></i> Assessment Scores
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Track performance, passing rates, and proficiency levels.</p>
                </div>
            </div>

            <!-- Tabs & Content -->
            <div x-data="{ activeTab: 'records', selectedAssessment: null }">
                
                <!-- Tab Navigation -->
                <div class="border-b border-slate-200 mb-8">
                    <nav class="flex space-x-8" aria-label="Tabs">
                        <button 
                            @click="activeTab = 'records'"
                            :class="activeTab === 'records' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                            <i class='bx bx-list-ul'></i> Assessment Records
                        </button>
                        <button 
                            @click="activeTab = 'analytics'"
                            :class="activeTab === 'analytics' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                            <i class='bx bx-stats'></i> Analytics
                        </button>
                    </nav>
                </div>

                <!-- Tab 1: Assessment Records -->
                <div x-show="activeTab === 'records'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    
                    @if($scoresData->isEmpty())
                        <div class="text-center py-20 bg-white rounded-2xl border border-slate-200 border-dashed">
                            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                                <i class='bx bx-folder-open'></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">No Assessments Found</h3>
                            <p class="text-slate-500 text-sm">Create an assessment to see scores here.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($scoresData as $data)
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col group">
                                    <!-- Card Image (Course Background) -->
                                    <div class="h-32 bg-slate-100 relative overflow-hidden">
                                        @if($data['course_picture'])
                                            <img src="{{ asset('storage/' . $data['course_picture']) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" alt="Course Image">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-200">
                                                <i class='bx bxs-image text-4xl'></i>
                                            </div>
                                        @endif
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-white/90 backdrop-blur text-slate-700 text-xs font-bold px-2 py-1 rounded shadow-sm">
                                                {{ $data['course_title'] }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Card Content -->
                                    <div class="p-5 flex-1 flex flex-col">
                                        <h3 class="font-bold text-slate-800 text-lg mb-1 line-clamp-1">{{ $data['title'] }}</h3>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                                                {{ $data['proficiency'] }}
                                            </span>
                                            <span class="text-slate-400 text-xs">•</span>
                                            <span class="text-slate-500 text-xs font-medium">{{ $data['total_scores'] }} pts</span>
                                        </div>

                                        <div class="mt-auto space-y-4">
                                            <!-- Stats Row -->
                                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                                <div class="text-center">
                                                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Pass Rate</span>
                                                    <span class="block text-sm font-bold text-emerald-600">{{ $data['passing_rate'] }}%</span>
                                                </div>
                                                <div class="w-px h-6 bg-slate-200"></div>
                                                <div class="text-center">
                                                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Takers</span>
                                                    <span class="block text-sm font-bold text-blue-600">{{ $data['participants'] }}</span>
                                                </div>
                                            </div>

                                            <button 
                                                @click="selectedAssessment = {{ json_encode($data) }}"
                                                class="w-full py-2.5 bg-white border border-blue-200 text-blue-600 font-bold rounded-xl hover:bg-blue-50 hover:border-blue-300 transition flex items-center justify-center gap-2 text-sm">
                                                <i class='bx bx-show'></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tab 2: Analytics -->
                <div x-show="activeTab === 'analytics'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($scoresData as $data)
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-6">
                                <!-- Circular Progress (Visual Representation) -->
                                <div class="relative w-20 h-20 flex-shrink-0">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-slate-100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                                        <path class="{{ $data['passing_rate'] >= 75 ? 'text-emerald-500' : ($data['passing_rate'] >= 50 ? 'text-amber-500' : 'text-rose-500') }}" 
                                              stroke-dasharray="{{ $data['passing_rate'] }}, 100" 
                                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                              fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                                        <span class="text-sm font-bold text-slate-800">{{ $data['passing_rate'] }}%</span>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-slate-800 truncate mb-1">{{ $data['title'] }}</h4>
                                    <p class="text-xs text-slate-500 mb-3">{{ $data['course_title'] }}</p>
                                    
                                    <!-- Linear Progress Bar -->
                                    <div class="w-full bg-slate-100 rounded-full h-2 mb-1">
                                        <div class="h-2 rounded-full {{ $data['passing_rate'] >= 75 ? 'bg-emerald-500' : ($data['passing_rate'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                             style="width: {{ $data['passing_rate'] }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 font-bold uppercase">
                                        <span>Passing Rate</span>
                                        <span>{{ $data['participants'] }} Participants</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal -->
                <div 
                    x-show="selectedAssessment" 
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
                    
                    <div 
                        @click.away="selectedAssessment = null"
                        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200">
                        
                        <!-- Modal Header -->
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <h3 class="font-bold text-lg text-slate-800" x-text="selectedAssessment?.title"></h3>
                            <button @click="selectedAssessment = null" class="text-slate-400 hover:text-slate-600">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-6 space-y-6">
                            <!-- Key Metrics -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 text-center">
                                    <span class="block text-xs font-bold text-blue-400 uppercase mb-1">Total Scores</span>
                                    <span class="block text-2xl font-bold text-blue-700" x-text="selectedAssessment?.total_scores"></span>
                                </div>
                                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                                    <span class="block text-xs font-bold text-emerald-400 uppercase mb-1">Passing Rate</span>
                                    <span class="block text-2xl font-bold text-emerald-700" x-text="selectedAssessment?.passing_rate + '%'"></span>
                                </div>
                            </div>

                            <!-- Details List -->
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between py-2 border-b border-slate-100">
                                    <span class="text-slate-500">Participants</span>
                                    <span class="font-bold text-slate-800" x-text="selectedAssessment?.participants"></span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100">
                                    <span class="text-slate-500">Organizational Scope</span>
                                    <span class="font-bold text-slate-800 capitalize" x-text="selectedAssessment?.scope"></span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100">
                                    <span class="text-slate-500">Proficiency Level</span>
                                    <span class="font-bold text-slate-800 capitalize" x-text="selectedAssessment?.proficiency"></span>
                                </div>
                            </div>

                            <!-- Skills -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Skills Assessed</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="skill in selectedAssessment?.skills" :key="skill">
                                        <span class="px-2 py-1 bg-slate-100 text-slate-600 text-xs rounded-md font-medium" x-text="skill"></span>
                                    </template>
                                    <template x-if="!selectedAssessment?.skills || selectedAssessment?.skills.length === 0">
                                        <span class="text-slate-400 italic text-xs">No specific skills tagged</span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                            <button @click="selectedAssessment = null" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 font-bold hover:bg-slate-50 transition">
                                Close
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <!-- Ion Icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>