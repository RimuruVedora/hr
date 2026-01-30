<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Training Schedule - EduFlow</title>
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    @include('partials.Employee-sidebar')

    <div class="main-content transition-all duration-300" style="margin-left: 260px;">
        <!-- Main Content Area -->
        <main class="p-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">My Training Schedule</h1>
                <p class="text-gray-500 text-sm mt-1">Manage your upcoming trainings and view history.</p>
            </div>

            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Active Training Schedule -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Training Schedule</p>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $activeCount }}</h3>
                    </div>
                </div>

                <!-- Training Attended -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="p-3 rounded-full bg-emerald-50 text-emerald-600">
                        <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Training Attended</p>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $attendedCount }}</h3>
                    </div>
                </div>

                <!-- Course Enrolled -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Course Enrolled</p>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $enrolledCoursesCount }}</h3>
                    </div>
                </div>
            </div>

            <!-- Training Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <h2 class="font-bold text-gray-800">Training List</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Training Title</th>
                                <th class="px-6 py-3 font-semibold">Course</th>
                                <th class="px-6 py-3 font-semibold">Type</th>
                                <th class="px-6 py-3 font-semibold">Location</th>
                                <th class="px-6 py-3 font-semibold">Start Date</th>
                                <th class="px-6 py-3 font-semibold">End Date</th>
                                <th class="px-6 py-3 font-semibold text-center">Proficiency</th>
                                <th class="px-6 py-3 font-semibold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($myTrainings as $training)
                                @php
                                    $now = now();
                                    $status = 'Past';
                                    $statusClass = 'bg-gray-100 text-gray-600';
                                    $dotClass = 'bg-gray-400';
                                    
                                    if ($training->end_date < $now) {
                                        $status = 'Past';
                                        $statusClass = 'bg-gray-100 text-gray-600';
                                        $dotClass = 'bg-gray-400';
                                    } elseif ($training->start_date > $now) {
                                        $status = 'Soon';
                                        $statusClass = 'bg-amber-100 text-amber-700';
                                        $dotClass = 'bg-amber-500';
                                    } else {
                                        $status = 'Ongoing';
                                        $statusClass = 'bg-emerald-100 text-emerald-700';
                                        $dotClass = 'bg-emerald-500';
                                    }

                                    // Format Type
                                    $typeDisplay = match($training->training_type) {
                                        'physical' => 'Physical',
                                        'online_exam' => 'Online Exam',
                                        'both' => 'Physical & Online',
                                        default => ucfirst($training->training_type)
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $training->title }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $training->course ? $training->course->title : 'N/A' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $typeDisplay }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $training->location ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ $training->start_date->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ $training->end_date->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-600 capitalize">
                                            {{ $training->proficiency }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i data-lucide="calendar-x" class="w-12 h-12 mb-3 text-gray-300"></i>
                                            <p>No training history found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination if needed -->
                @if(method_exists($myTrainings, 'links'))
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $myTrainings->links() }}
                    </div>
                @endif
            </div>

        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>