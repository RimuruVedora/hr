<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Scores</title>
    @vite(['resources/css/app.css'])
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .modal-enter { opacity: 0; transform: scale(0.9); }
        .modal-enter-active { transition: opacity 0.3s ease-out, transform 0.3s ease-out; }
        .modal-leave-active { transition: opacity 0.2s ease-in, transform 0.2s ease-in; }
        .modal-leave-to { opacity: 0; transform: scale(0.9); }
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
                    <button id="sidebarToggle" class="lg:hidden text-slate-500 hover:text-blue-600 transition-colors">
                        <i class='bx bx-menu text-2xl'></i>
                    </button>
                    <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class='bx bx-bar-chart-alt-2 text-blue-600 text-2xl'></i>
                        Assessment Scores
                    </h1>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full" 
              x-data="assessmentScoreApp()">
            
            <!-- Breadcrumb Navigation -->
            <nav class="flex mb-8" aria-label="Breadcrumb" x-show="view !== 'courses'">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="#" @click.prevent="view = 'courses'" class="inline-flex items-center text-sm font-medium text-slate-700 hover:text-blue-600">
                            <i class='bx bx-book-open mr-2'></i>
                            Courses
                        </a>
                    </li>
                    <li x-show="view === 'trainings'">
                        <div class="flex items-center">
                            <i class='bx bx-chevron-right text-slate-400 mx-1'></i>
                            <span class="text-sm font-medium text-slate-500" x-text="selectedCourseTitle"></span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- View 1: Courses List -->
            <div x-show="view === 'courses'" x-transition.opacity>
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-slate-800">Select a Course</h2>
                    <p class="text-slate-500 text-sm">Choose a course to view its training schedules.</p>
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
                                        <img src="{{ asset('storage/' . $course->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" alt="Course Image">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-200">
                                            <i class='bx bxs-image text-4xl'></i>
                                        </div>
                                    @endif
                                    <div class="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-t from-black/50 to-transparent"></div>
                                    <div class="absolute bottom-3 left-4 text-white font-bold text-lg drop-shadow-md line-clamp-1">
                                        {{ $course->title }}
                                    </div>
                                </div>
                                <div class="p-5">
                                    <p class="text-slate-500 text-sm mb-4 line-clamp-2">{{ $course->description }}</p>
                                    <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                                        <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded border border-blue-100">{{ $course->trainings_count }} Trainings</span>
                                        <span class="flex items-center gap-1 group-hover:translate-x-1 transition-transform duration-300 text-slate-400 group-hover:text-blue-600">
                                            Select <i class='bx bx-right-arrow-alt'></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- View 2: Trainings List -->
            <div x-show="view === 'trainings'" x-cloak x-transition.opacity>
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-slate-800">Training Schedules</h2>
                    <p class="text-slate-500 text-sm">Select a training to view participant scores.</p>
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
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer p-6 relative overflow-hidden group"
                             @click="openScoreModal(training.id)">
                            
                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4">
                                <span class="text-xs font-bold px-2 py-1 rounded uppercase tracking-wider"
                                      :class="{
                                          'bg-emerald-100 text-emerald-700': training.status === 'completed',
                                          'bg-blue-100 text-blue-700': training.status === 'ongoing',
                                          'bg-amber-100 text-amber-700': training.status === 'scheduled',
                                          'bg-slate-100 text-slate-700': !['completed', 'ongoing', 'scheduled'].includes(training.status)
                                      }" x-text="training.status"></span>
                            </div>

                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                                    <i class='bx bx-calendar-event'></i>
                                </div>
                            </div>
                            
                            <h3 class="font-bold text-slate-800 text-lg mb-3 pr-16" x-text="training.title || 'Training Session'"></h3>
                            
                            <div class="space-y-2 text-sm text-slate-500 border-t border-slate-100 pt-3">
                                <div class="flex items-center gap-2">
                                    <i class='bx bx-time text-slate-400'></i>
                                    <span x-text="formatDate(training.start_date) + ' - ' + formatDate(training.end_date)"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class='bx bx-map text-slate-400'></i>
                                    <span x-text="training.location || 'Online'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class='bx bx-user text-slate-400'></i>
                                    <span x-text="training.participants_count + ' Participants'"></span>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-3 text-center">
                                <span class="text-blue-600 text-sm font-medium group-hover:underline">View Scores</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal: Scores Pop-out -->
            <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div x-show="showModal" 
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                         @click="showModal = false" aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal panel -->
                    <div x-show="showModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center gap-2" id="modal-title">
                                        <i class='bx bx-trophy text-yellow-500'></i>
                                        Participant Scores
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Showing scores for <span class="font-bold text-gray-700" x-text="selectedTrainingTitle"></span>
                                        </p>

                                        <!-- Loading State -->
                                        <div x-show="isModalLoading" class="py-10 flex justify-center">
                                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                        </div>

                                        <!-- Empty State -->
                                        <div x-show="!isModalLoading && scores.length === 0" class="text-center py-8 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                                            <p class="text-slate-500">No participants found.</p>
                                        </div>

                                        <!-- Scores Table -->
                                        <div x-show="!isModalLoading && scores.length > 0" class="overflow-hidden border border-slate-200 rounded-lg">
                                            <table class="min-w-full divide-y divide-slate-200">
                                                <thead class="bg-slate-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Employee</th>
                                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Online Score</th>
                                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Physical Score</th>
                                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-slate-200">
                                                    <template x-for="score in scores" :key="score.employee_id">
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="flex items-center">
                                                                    <div class="flex-shrink-0 h-8 w-8">
                                                                        <template x-if="score.avatar">
                                                                            <img class="h-8 w-8 rounded-full object-cover" :src="'/storage/' + score.avatar" onerror="this.onerror=null;this.src='{{ asset('assets/images/logo.png') }}';" alt="">
                                                                        </template>
                                                                        <template x-if="!score.avatar">
                                                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs" x-text="getInitials(score.name)"></div>
                                                                        </template>
                                                                    </div>
                                                                    <div class="ml-4">
                                                                        <div class="text-sm font-medium text-gray-900" x-text="score.name"></div>
                                                                        <div class="text-xs text-gray-500" x-text="score.department"></div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700" x-text="score.online_score"></span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700" x-text="score.physical_score"></span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                                      :class="{
                                                                          'bg-green-100 text-green-800': score.status === 'Completed' || score.status === 'Passed',
                                                                          'bg-red-100 text-red-800': score.status === 'Failed',
                                                                          'bg-gray-100 text-gray-800': !['Completed', 'Passed', 'Failed'].includes(score.status)
                                                                      }" x-text="score.status || 'Enrolled'">
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" @click="showModal = false">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        function assessmentScoreApp() {
            return {
                view: 'courses', // courses, trainings
                isLoading: false,
                selectedCourseTitle: '',
                selectedTrainingTitle: '',
                trainings: [],
                scores: [],
                
                // Modal State
                showModal: false,
                isModalLoading: false,

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

                async openScoreModal(trainingId) {
                    this.showModal = true;
                    this.isModalLoading = true;
                    this.scores = []; // Reset scores
                    
                    try {
                        const response = await fetch(`{{ route('learning.training.scores', ':id') }}`.replace(':id', trainingId));
                        const data = await response.json();
                        this.scores = data.scores;
                        this.selectedTrainingTitle = data.training_title;
                    } catch (error) {
                        console.error('Error fetching scores:', error);
                        alert('Failed to load scores.');
                    } finally {
                        this.isModalLoading = false;
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
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
