<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talent Assessment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    @include('partials.admin-sidebar')

    <div class="main-content ml-0 md:ml-64 transition-all duration-300">
        <main class="max-w-7xl mx-auto p-4 md:p-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Talent Assessment</h1>
                <p class="text-slate-500 mt-1">Assess employee capability and identify top talent for succession.</p>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                            <i class='bx bx-user text-xl'></i>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 bg-slate-100 text-slate-600 rounded">Total</span>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Employees Evaluated</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $totalEmployees }}</h3>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                            <i class='bx bx-bar-chart-alt-2 text-xl'></i>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 bg-emerald-50 text-emerald-600 rounded">Avg</span>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Competency Score</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($avgCompetencyScore, 1) }}</h3>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                            <i class='bx bx-check-shield text-xl'></i>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 bg-purple-50 text-purple-600 rounded">Total</span>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Exams Passed</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $totalPassedExams }}</h3>
                </div>

                <!-- Card 4 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-orange-50 text-orange-600 rounded-lg">
                            <i class='bx bx-certification text-xl'></i>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 bg-orange-50 text-orange-600 rounded">Total</span>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Trainings Completed</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $totalCompletedTrainings }}</h3>
                </div>
            </div>

            <!-- Top Lists Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Top Skilled Employees -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <i class='bx bx-trending-up mr-2 text-blue-600'></i> Top Skilled Employees
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            @foreach($topSkilled as $employee)
                                <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-lg transition-colors border border-transparent hover:border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $employee->jobRole->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-blue-600">{{ $employee->total_proficiency }}</p>
                                        <p class="text-[10px] text-slate-400 uppercase">Points</p>
                                    </div>
                                </div>
                            @endforeach
                            @if($topSkilled->isEmpty())
                                <p class="text-center text-sm text-slate-500 py-4">No data available.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Top Exam Passers -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <i class='bx bx-award mr-2 text-purple-600'></i> Most Exams Passed
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            @foreach($topExamPassers as $employee)
                                <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-lg transition-colors border border-transparent hover:border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-sm">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $employee->jobRole->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-purple-600">{{ $employee->passed_exams_count }}</p>
                                        <p class="text-[10px] text-slate-400 uppercase">Passed</p>
                                    </div>
                                </div>
                            @endforeach
                            @if($topExamPassers->isEmpty())
                                <p class="text-center text-sm text-slate-500 py-4">No data available.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Top Training Completers -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <i class='bx bx-book-reader mr-2 text-orange-600'></i> Most Trainings Completed
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            @foreach($topTrainingCompleters as $employee)
                                <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-lg transition-colors border border-transparent hover:border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm">
                                            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $employee->jobRole->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-orange-600">{{ $employee->completed_trainings_count }}</p>
                                        <p class="text-[10px] text-slate-400 uppercase">Trainings</p>
                                    </div>
                                </div>
                            @endforeach
                            @if($topTrainingCompleters->isEmpty())
                                <p class="text-center text-sm text-slate-500 py-4">No data available.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>