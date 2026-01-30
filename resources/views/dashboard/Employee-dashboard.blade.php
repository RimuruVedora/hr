<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - HR System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/dashboard/dashboard.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <!-- Bootstrap CSS for Sidebar compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Custom scrollbar for modern feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen">
    
    @include('partials.Employee-sidebar')

    <div class="main-content">
        <main class="p-6 lg:p-12 max-w-[1600px] mx-auto" x-data="dashboardData()">
            
            <!-- Dashboard Intro -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">Welcome back, {{ Auth::user()->email }}</h1>
                    <p class="text-slate-500 mt-2 font-medium text-lg">Track your learning progress and skill development.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Courses Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Courses Completed</p>
                        <h3 class="text-3xl font-bold text-slate-800">{{ $stats['courses'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                        <ion-icon name="book-outline"></ion-icon>
                    </div>
                </div>

                <!-- Ongoing Training Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Ongoing Training</p>
                        <h3 class="text-3xl font-bold text-slate-800">{{ $stats['ongoing'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                        <ion-icon name="time-outline"></ion-icon>
                    </div>
                </div>

                <!-- Acquired Skills Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-all">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Acquired Skills</p>
                        <h3 class="text-3xl font-bold text-slate-800">{{ $stats['skills'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                        <ion-icon name="ribbon-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Activity Tabs -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-[500px]">
                    <!-- Tab Navigation -->
                    <div class="flex border-b border-slate-100">
                        <button @click="activeTab = 'activities'" 
                            :class="{'border-b-2 border-blue-600 text-blue-600': activeTab === 'activities', 'text-slate-500 hover:text-slate-700': activeTab !== 'activities'}"
                            class="flex-1 py-4 text-sm font-bold uppercase tracking-wider transition-colors">
                            Activities (To Do)
                        </button>
                        <button @click="activeTab = 'recent'" 
                            :class="{'border-b-2 border-blue-600 text-blue-600': activeTab === 'recent', 'text-slate-500 hover:text-slate-700': activeTab !== 'recent'}"
                            class="flex-1 py-4 text-sm font-bold uppercase tracking-wider transition-colors">
                            Recent Activities
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="flex-1 overflow-y-auto p-6 relative">
                        <!-- Activities Tab -->
                        <div x-show="activeTab === 'activities'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            @if(count($activities) > 0)
                                <div class="space-y-4">
                                    @foreach($activities as $activity)
                                        <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/50 transition-all group">
                                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                                <ion-icon name="calendar-outline"></ion-icon>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-bold text-slate-800 text-sm">{{ $activity['title'] }}</h4>
                                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $activity['description'] }}</p>
                                                <div class="flex items-center gap-3 mt-3">
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $activity['type'] }}</span>
                                                    <span class="text-[10px] text-slate-400 flex items-center gap-1">
                                                        <ion-icon name="time-outline"></ion-icon> {{ $activity['date'] }}
                                                    </span>
                                                </div>
                                            </div>
                                            @if($activity['is_exam_completed'])
                                                <button disabled class="px-3 py-1.5 text-xs font-bold bg-emerald-100 text-emerald-600 rounded-lg cursor-not-allowed flex items-center gap-1">
                                                    <ion-icon name="checkmark-circle"></ion-icon> Done
                                                </button>
                                            @elseif($activity['has_exam'])
                                                <form action="{{ route('training.start', $activity['id']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">Start</button>
                                                </form>
                                            @else
                                                 <span class="px-3 py-1.5 text-xs font-bold bg-gray-100 text-gray-400 rounded-lg cursor-default">No Exam</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-64 text-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 text-3xl mb-3">
                                        <ion-icon name="checkmark-done-outline"></ion-icon>
                                    </div>
                                    <p class="text-slate-500 font-medium text-sm">All caught up! No pending activities.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Recent Activities Tab -->
                        <div x-show="activeTab === 'recent'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            @if(count($recentActivities) > 0)
                                <div class="relative pl-4 border-l-2 border-slate-100 space-y-8 my-2">
                                    @foreach($recentActivities as $recent)
                                        <div class="relative">
                                            <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-white border-2 border-slate-300 ring-4 ring-white"></div>
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ $recent['date'] }}</span>
                                                <h4 class="font-bold text-slate-800 text-sm">{{ $recent['title'] }}</h4>
                                                <p class="text-xs text-emerald-600 font-medium mt-0.5 flex items-center gap-1">
                                                    <ion-icon name="checkmark-circle-outline"></ion-icon> {{ $recent['status'] }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-64 text-center">
                                    <p class="text-slate-400 text-sm">No recent activities found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: Skill Gap Graph -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col h-[500px]">
                    <div class="mb-6">
                        <h3 class="font-bold text-slate-800 text-lg">Skill Gap Analysis</h3>
                        <p class="text-xs text-slate-500">Current vs Target Proficiency Levels</p>
                    </div>
                    <div class="flex-1 relative w-full h-full">
                        <canvas id="skillGapChart"></canvas>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardData', () => ({
                activeTab: 'activities',
                init() {
                    this.initChart();
                },
                initChart() {
                    const ctx = document.getElementById('skillGapChart');
                    if (!ctx) return;

                    const data = @json($skillGapData);
                    
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels.length ? data.labels : ['Communication', 'Leadership', 'Technical', 'Management', 'Analysis'],
                            datasets: [{
                                label: 'Current Proficiency',
                                data: data.current.length ? data.current : [2, 3, 4, 2, 3],
                                borderColor: '#3b82f6', // blue-500
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#3b82f6',
                                pointHoverBackgroundColor: '#3b82f6',
                                pointHoverBorderColor: '#fff'
                            }, {
                                label: 'Target Proficiency',
                                data: data.target.length ? data.target : [4, 4, 5, 3, 4],
                                borderColor: '#94a3b8', // slate-400
                                borderDash: [5, 5],
                                borderWidth: 2,
                                tension: 0.4,
                                fill: false,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#94a3b8'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8,
                                        font: { size: 11, family: 'sans-serif' }
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    padding: 10,
                                    cornerRadius: 8,
                                    titleFont: { size: 12 },
                                    bodyFont: { size: 11 }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 5,
                                    ticks: { stepSize: 1, font: { size: 10 } },
                                    grid: { color: '#f1f5f9' }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 10 } }
                                }
                            }
                        }
                    });
                }
            }));
        });
    </script>
</body>
</html>