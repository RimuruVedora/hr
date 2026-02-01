<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall Performance Scores</title>
    @vite(['resources/css/app.css'])
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased">

    <!-- Sidebar -->
    @include('partials.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content ml-0 md:ml-64 transition-all duration-300 min-h-screen flex flex-col">
        
        <!-- Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class='bx bx-bar-chart-alt-2 text-blue-600 text-2xl'></i>
                        Overall Performance Scores
                    </h1>
                </div>
                <!-- User Profile / Actions if needed -->
            </div>
        </header>

        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full" 
              x-data="overallScoreApp()">
            
            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Courses -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                            <i class='bx bx-book-open text-xl'></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalCourses }}</h3>
                    <p class="text-sm text-slate-500 font-medium">Total Courses</p>
                </div>

                <!-- Total Trainings -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg">
                            <i class='bx bx-calendar-event text-xl'></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalTrainings }}</h3>
                    <p class="text-sm text-slate-500 font-medium">Scheduled Trainings</p>
                </div>

                <!-- Avg Online Score -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                            <i class='bx bx-laptop text-xl'></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">{{ number_format($avgOnlineScore, 1) }}</h3>
                    <p class="text-sm text-slate-500 font-medium">Avg Online Score</p>
                </div>

                <!-- Avg Physical Score -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                            <i class='bx bx-run text-xl'></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">{{ number_format($avgPhysicalScore, 1) }}</h3>
                    <p class="text-sm text-slate-500 font-medium">Avg Physical Score</p>
                </div>
            </div>

            <!-- View: Courses List -->
            <div x-show="view === 'courses'" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-800">Select a Course</h2>
                </div>

                @if($courses->isEmpty())
                    <div class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
                        <i class='bx bx-folder-open text-4xl text-slate-300 mb-3'></i>
                        <p class="text-slate-500">No courses available.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($courses as $course)
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer group"
                                 @click="loadTrainings({{ $course->id }}, '{{ addslashes($course->title) }}')">
                                <div class="h-32 bg-slate-100 relative overflow-hidden rounded-t-xl">
                                    @if($course->image)
                                        <img src="{{ asset('storage/' . $course->image) }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/logo.png') }}';" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" alt="Course Image">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-200">
                                            <i class='bx bxs-image text-4xl'></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <h3 class="font-bold text-slate-800 text-lg mb-2 line-clamp-1 group-hover:text-blue-600 transition">{{ $course->title }}</h3>
                                    <p class="text-slate-500 text-sm mb-4 line-clamp-2">{{ $course->description }}</p>
                                    <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                                        <span class="bg-slate-100 px-2 py-1 rounded">{{ $course->trainings_count }} Trainings</span>
                                        <span class="flex items-center gap-1 text-blue-600">
                                            View Details <i class='bx bx-right-arrow-alt'></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- View: Trainings List -->
            <div x-show="view === 'trainings'" x-cloak x-transition.opacity>
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'courses'" class="p-2 hover:bg-slate-100 rounded-full transition text-slate-500 hover:text-slate-800">
                        <i class='bx bx-arrow-back text-xl'></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Trainings for <span x-text="selectedCourseTitle" class="text-blue-600"></span></h2>
                        <p class="text-sm text-slate-500">Select a schedule to view scores</p>
                    </div>
                </div>

                <div x-show="isLoading" class="py-12 flex justify-center">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                </div>

                <div x-show="!isLoading && trainings.length === 0" class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
                    <i class='bx bx-calendar-x text-4xl text-slate-300 mb-3'></i>
                    <p class="text-slate-500">No trainings found for this course.</p>
                </div>

                <div x-show="!isLoading && trainings.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="training in trainings" :key="training.id">
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer p-6"
                             @click="loadScores(training.id)">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="text-xs font-bold px-2 py-1 rounded uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': training.status === 'completed',
                                              'bg-blue-100 text-blue-700': training.status === 'ongoing',
                                              'bg-amber-100 text-amber-700': training.status === 'scheduled',
                                              'bg-slate-100 text-slate-700': !['completed', 'ongoing', 'scheduled'].includes(training.status)
                                          }" x-text="training.status"></span>
                                </div>
                                <div class="text-slate-400">
                                    <i class='bx bx-chevron-right text-xl'></i>
                                </div>
                            </div>
                            
                            <h3 class="font-bold text-slate-800 text-lg mb-2" x-text="training.title || 'Training Session'"></h3>
                            
                            <div class="space-y-2 text-sm text-slate-500">
                                <div class="flex items-center gap-2">
                                    <i class='bx bx-calendar text-slate-400'></i>
                                    <span x-text="formatDate(training.start_date)"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class='bx bx-map text-slate-400'></i>
                                    <span x-text="training.location || 'Online'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class='bx bx-group text-slate-400'></i>
                                    <span x-text="training.participants_count + ' Participants'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- View: Scores List -->
            <div x-show="view === 'scores'" x-cloak x-transition.opacity>
                <div class="flex items-center gap-4 mb-6">
                    <button @click="view = 'trainings'" class="p-2 hover:bg-slate-100 rounded-full transition text-slate-500 hover:text-slate-800">
                        <i class='bx bx-arrow-back text-xl'></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Scores: <span x-text="selectedTrainingTitle" class="text-blue-600"></span></h2>
                        <p class="text-sm text-slate-500">Combined scores from Online Exams and Physical Evaluations</p>
                    </div>
                </div>

                <div x-show="isLoading" class="py-12 flex justify-center">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                </div>

                <div x-show="!isLoading && scores.length === 0" class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
                    <i class='bx bx-user-x text-4xl text-slate-300 mb-3'></i>
                    <p class="text-slate-500">No participants found for this training.</p>
                </div>

                <div x-show="!isLoading && scores.length > 0" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="px-6 py-4">Employee</th>
                                    <th class="px-6 py-4">Department / Role</th>
                                    <th class="px-6 py-4 text-center">Online Score</th>
                                    <th class="px-6 py-4 text-center">Physical Score</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="score in scores" :key="score.employee_id">
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-slate-200 flex-shrink-0 overflow-hidden">
                                                    <template x-if="score.avatar">
                                                        <img :src="'/storage/' + score.avatar" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!score.avatar">
                                                        <div class="w-full h-full flex items-center justify-center bg-blue-100 text-blue-600 font-bold text-sm" x-text="getInitials(score.name)"></div>
                                                    </template>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800" x-text="score.name"></div>
                                                    <div class="text-xs text-slate-500" x-text="'ID: ' + score.employee_id"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-800" x-text="score.department"></div>
                                            <div class="text-xs text-slate-500" x-text="score.job_role"></div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                  x-text="score.online_score">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"
                                                  x-text="score.physical_score">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-800': score.status === 'Completed' || score.status === 'Passed',
                                                      'bg-red-100 text-red-800': score.status === 'Failed',
                                                      'bg-slate-100 text-slate-800': !['Completed', 'Passed', 'Failed'].includes(score.status)
                                                  }"
                                                  x-text="score.status || 'Enrolled'">
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        function overallScoreApp() {
            return {
                view: 'courses', // courses, trainings, scores
                isLoading: false,
                selectedCourseTitle: '',
                selectedTrainingTitle: '',
                trainings: [],
                scores: [],

                async loadTrainings(courseId, courseTitle) {
                    this.isLoading = true;
                    this.selectedCourseTitle = courseTitle;
                    this.view = 'trainings';
                    
                    try {
                        const response = await fetch(`{{ route('learning.course.trainings', ':id') }}`.replace(':id', courseId));
                        this.trainings = await response.json();
                    } catch (error) {
                        console.error('Error fetching trainings:', error);
                        alert('Failed to load trainings.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async loadScores(trainingId) {
                    this.isLoading = true;
                    this.view = 'scores';
                    
                    try {
                        const response = await fetch(`{{ route('learning.training.scores', ':id') }}`.replace(':id', trainingId));
                        const data = await response.json();
                        this.scores = data.scores;
                        this.selectedTrainingTitle = data.training_title;
                    } catch (error) {
                        console.error('Error fetching scores:', error);
                        alert('Failed to load scores.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return 'TBD';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                },

                getInitials(name) {
                    if (!name) return '?';
                    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                }
            }
        }
    </script>
</body>
</html>
