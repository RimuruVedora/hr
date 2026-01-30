<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    @include('partials.admin-sidebar')

    <div class="main-content ml-0 md:ml-64 transition-all duration-300">
        <main class="max-w-7xl mx-auto p-4 md:p-8" 
              x-data="{ 
                  storageUrl: '{{ asset('storage') }}',
                  activeTab: 'published',
                  search: '',
                  scopeFilter: 'all',
                  createModalOpen: {{ $errors->any() ? 'true' : 'false' }},
                  viewModalOpen: false,
                  startModalOpen: false,
                  otp: '',
                  otpSent: false,
                  otpLoading: false,
                  selectedTraining: null,
                  courses: {{ $courses->toJson() }},
                  selectedCourseId: '{{ old('course_id') }}',
                  trainingType: '{{ old('training_type', 'physical') }}',
                  async sendOtp() {
                      if (!this.selectedTraining) return;
                      this.otpLoading = true;
                      try {
                          const response = await fetch('{{ route('training.send-otp') }}', {
                              method: 'POST',
                              headers: {
                                  'Content-Type': 'application/json',
                                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
                              },
                              body: JSON.stringify({ training_id: this.selectedTraining.id })
                          });
                          const data = await response.json();
                          if (data.success) {
                              this.otpSent = true;
                              alert(data.message);
                          } else {
                              alert(data.message);
                          }
                      } catch (error) {
                          console.error('Error:', error);
                          alert('Failed to send OTP. Please try again.');
                      } finally {
                          this.otpLoading = false;
                      }
                  },
                  get courseSkills() {
                      if(!this.selectedCourseId) return [];
                      const course = this.courses.find(c => c.id == this.selectedCourseId);
                      return course ? course.competencies : [];
                  },
                  get courseAssessments() {
                      if(!this.selectedCourseId) return [];
                      const course = this.courses.find(c => c.id == this.selectedCourseId);
                      return course && course.assessments ? course.assessments : [];
                  },
                  get filteredPublished() {
                      const trainings = {{ $publishedTrainings->toJson() }};
                      return trainings.filter(t => {
                          const title = t.title ? t.title.toLowerCase() : '';
                          const orgScope = t.org_scope ? t.org_scope.toLowerCase() : '';
                          const search = this.search.toLowerCase();
                          const filter = this.scopeFilter.toLowerCase();
                          
                          const matchesSearch = title.includes(search);
                          const matchesScope = this.scopeFilter === 'all' || orgScope === filter;
                          return matchesSearch && matchesScope;
                      });
                  },
                  get filteredPre() {
                      const trainings = {{ $preTrainings->toJson() }};
                      return trainings.filter(t => {
                          const title = t.title ? t.title.toLowerCase() : '';
                          const orgScope = t.org_scope ? t.org_scope.toLowerCase() : '';
                          const search = this.search.toLowerCase();
                          const filter = this.scopeFilter.toLowerCase();

                          const matchesSearch = title.includes(search);
                          const matchesScope = this.scopeFilter === 'all' || orgScope === filter;
                          return matchesSearch && matchesScope;
                      });
                  },
                  formatDate(dateStr) {
                      return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                  },
                  checkStartDate(dateStr) {
                      return new Date(dateStr) <= new Date();
                  }
              }">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                        <i class='bx bx-calendar-event text-blue-600'></i> Training Management
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Manage training schedules, participants, and analytics.</p>
                </div>
                <button @click="createModalOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-100 flex items-center gap-2 transition-all">
                    <i class='bx bx-plus'></i> Create Schedule
                </button>
            </div>

            <!-- Controls -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Tabs -->
                <div class="flex bg-slate-100 p-1 rounded-xl">
                    <button @click="activeTab = 'published'" :class="activeTab === 'published' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all">Published</button>
                    <button @click="activeTab = 'pre'" :class="activeTab === 'pre' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all">Pre-Training</button>
                    <button @click="activeTab = 'analytics'" :class="activeTab === 'analytics' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg text-sm font-bold transition-all">Analytics</button>
                </div>

                <!-- Filters -->
                <div class="flex gap-3 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400'></i>
                        <input x-model="search" type="text" placeholder="Search training..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <select x-model="scopeFilter" class="px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="all">All Scopes</option>
                        <option value="internal">Internal</option>
                        <option value="departmental">Departmental</option>
                        <option value="public">Public</option>
                    </select>
                </div>
            </div>

            <!-- Tab 1: Published Training -->
            <div x-show="activeTab === 'published'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-transition>
                <template x-for="training in filteredPublished" :key="training.id">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="h-40 bg-slate-100 relative overflow-hidden">
                            <template x-if="training.course.picture">
                                <img :src="storageUrl + '/' + training.course.picture" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </template>
                            <template x-if="!training.course.picture">
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-200">
                                    <i class='bx bxs-image text-4xl'></i>
                                </div>
                            </template>
                            <div class="absolute top-3 right-3">
                                <span class="bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded shadow-sm uppercase tracking-wider">Published</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-800 text-lg mb-2 line-clamp-1" x-text="training.title"></h3>
                            <div class="flex items-center gap-4 text-xs text-slate-500 mb-4 font-medium">
                                <div class="flex items-center gap-1"><i class='bx bx-group'></i> <span x-text="training.capacity + ' Capacity'"></span></div>
                                <div class="flex items-center gap-1"><i class='bx bx-user-check'></i> <span x-text="training.participants.length + ' Enrolled'"></span></div>
                            </div>
                            <button @click="selectedTraining = training; viewModalOpen = true" class="w-full py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors">View Details</button>
                        </div>
                    </div>
                </template>
                <div x-show="filteredPublished.length === 0" class="col-span-full text-center py-20 text-slate-400">
                    <i class='bx bx-calendar-x text-5xl mb-3'></i>
                    <p>No published trainings found.</p>
                </div>
            </div>

            <!-- Tab 2: Pre-Training -->
            <div x-show="activeTab === 'pre'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-transition x-cloak>
                <template x-for="training in filteredPre" :key="training.id">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="h-40 bg-slate-100 relative overflow-hidden">
                             <template x-if="training.course.picture">
                                <img :src="storageUrl + '/' + training.course.picture" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </template>
                            <template x-if="!training.course.picture">
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-200">
                                    <i class='bx bxs-image text-4xl'></i>
                                </div>
                            </template>
                            <div class="absolute top-3 right-3">
                                <span class="bg-amber-400 text-white text-xs font-bold px-2 py-1 rounded shadow-sm uppercase tracking-wider">Pre-Training</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-800 text-lg mb-1 line-clamp-1" x-text="training.title"></h3>
                            <p class="text-xs text-slate-400 font-bold mb-4" x-text="'Starts: ' + formatDate(training.start_date)"></p>
                            
                            <div class="flex items-center gap-4 text-xs text-slate-500 mb-4 font-medium">
                                <div class="flex items-center gap-1"><i class='bx bx-group'></i> <span x-text="training.capacity + ' Capacity'"></span></div>
                                <div class="flex items-center gap-1"><i class='bx bx-user-check'></i> <span x-text="training.participants.length + ' Enrolled'"></span></div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <button @click="selectedTraining = training; startModalOpen = true" class="py-2 rounded-lg bg-rose-50 text-rose-600 font-bold text-xs hover:bg-rose-100 transition-colors flex items-center justify-center gap-1">
                                    <i class='bx bx-rocket'></i> Immediate Start
                                </button>
                                <form :action="'/training/' + training.id + '/start'" method="POST">
                                    @csrf
                                    <button type="submit" :disabled="!checkStartDate(training.start_date)" class="w-full py-2 rounded-lg bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1">
                                        <i class='bx bx-play'></i> Start
                                    </button>
                                </form>
                            </div>
                            <button @click="selectedTraining = training; viewModalOpen = true" class="w-full py-2 rounded-lg border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">View Details</button>
                        </div>
                    </div>
                </template>
                <div x-show="filteredPre.length === 0" class="col-span-full text-center py-20 text-slate-400">
                    <i class='bx bx-calendar-event text-5xl mb-3'></i>
                    <p>No upcoming pre-trainings found.</p>
                </div>
            </div>

            <!-- Tab 3: Analytics -->
            <div x-show="activeTab === 'analytics'" class="space-y-4" x-transition x-cloak>
                @foreach($analytics as $stat)
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-6">
                    <div class="flex-1">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-bold text-slate-800">{{ $stat['title'] }}</h3>
                            <span class="text-xs font-bold text-slate-500">{{ $stat['participants'] }} / {{ $stat['capacity'] }} Participants</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full transition-all duration-1000" style="width: {{ $stat['progress'] }}%"></div>
                        </div>
                        <div class="flex justify-between mt-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                            <span>Duration: {{ $stat['duration'] }}</span>
                            <span>{{ $stat['progress'] }}% Complete</span>
                        </div>
                    </div>
                </div>
                @endforeach
                @if($analytics->isEmpty())
                    <div class="text-center py-20 text-slate-400">
                        <i class='bx bx-stats text-5xl mb-3'></i>
                        <p>No analytics data available.</p>
                    </div>
                @endif
            </div>

            <!-- Create Schedule Modal -->
            <div x-show="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.away="createModalOpen = false">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <h3 class="font-bold text-lg text-slate-800">Schedule New Training</h3>
                        <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600"><i class='bx bx-x text-2xl'></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar">
                        <form action="{{ route('training.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Training Title</label>
                                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm @error('title') border-red-500 @enderror">
                                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Course</label>
                                    <select name="course_id" x-model="selectedCourseId" required class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white @error('course_id') border-red-500 @enderror">
                                        <option value="">Select a Course</option>
                                        <template x-for="course in courses" :key="course.id">
                                            <option :value="course.id" x-text="course.title" :selected="course.id == '{{ old('course_id') }}'"></option>
                                        </template>
                                    </select>
                                    @error('course_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Training Type</label>
                                    <select name="training_type" x-model="trainingType" required class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white @error('training_type') border-red-500 @enderror">
                                        <option value="physical">Physical Training</option>
                                        <option value="online_exam">Online Exam</option>
                                        <option value="both">Both</option>
                                    </select>
                                    @error('training_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-2" x-show="trainingType === 'online_exam' || trainingType === 'both'" x-transition>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Assessment (Exam)</label>
                                    <select name="assessment_id" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white @error('assessment_id') border-red-500 @enderror" :required="trainingType === 'online_exam' || trainingType === 'both'">
                                        <option value="">Select an Exam</option>
                                        <template x-for="assessment in courseAssessments" :key="assessment.id">
                                            <option :value="assessment.id" x-text="assessment.title" :selected="assessment.id == '{{ old('assessment_id') }}'"></option>
                                        </template>
                                    </select>
                                    <p x-show="courseAssessments.length === 0 && selectedCourseId" class="text-xs text-red-500 mt-1">No assessments found for this course.</p>
                                    @error('assessment_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-2" x-show="trainingType === 'physical' || trainingType === 'both'" x-transition>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Location</label>
                                    <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Conference Room A or 123 Main St" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm @error('location') border-red-500 @enderror">
                                    @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Start Date</label>
                                    <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm text-slate-600 @error('start_date') border-red-500 @enderror">
                                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">End Date</label>
                                    <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" required class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm text-slate-600 @error('end_date') border-red-500 @enderror">
                                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Capacity</label>
                                    <input type="number" name="capacity" value="{{ old('capacity') }}" required min="1" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm @error('capacity') border-red-500 @enderror">
                                    @error('capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Duration</label>
                                    <div class="flex gap-2">
                                        <input type="number" name="duration_value" value="{{ old('duration_value') }}" required min="1" placeholder="e.g. 2" class="flex-1 px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm @error('duration_value') border-red-500 @enderror">
                                        <select name="duration_unit" class="w-24 px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white @error('duration_unit') border-red-500 @enderror">
                                            <option value="Hours" {{ old('duration_unit') == 'Hours' ? 'selected' : '' }}>Hours</option>
                                            <option value="Days" {{ old('duration_unit') == 'Days' ? 'selected' : '' }}>Days</option>
                                        </select>
                                    </div>
                                    @error('duration_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    @error('duration_unit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Organizational Scope</label>
                                    <select name="org_scope" required class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white @error('org_scope') border-red-500 @enderror">
                                        <option value="Internal" {{ old('org_scope') == 'Internal' ? 'selected' : '' }}>Internal</option>
                                        <option value="Departmental" {{ old('org_scope') == 'Departmental' ? 'selected' : '' }}>Departmental</option>
                                        <option value="Public" {{ old('org_scope') == 'Public' ? 'selected' : '' }}>Public</option>
                                    </select>
                                    @error('org_scope') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Proficiency</label>
                                    <select name="proficiency" required class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white @error('proficiency') border-red-500 @enderror">
                                        <option value="Beginner" {{ old('proficiency') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="Intermediate" {{ old('proficiency') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="Advanced" {{ old('proficiency') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                    </select>
                                    @error('proficiency') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-2" x-show="courseSkills.length > 0">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Skills (from Course)</label>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="skill in courseSkills" :key="skill.id">
                                            <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-xs font-bold rounded-lg" x-text="skill.name"></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description</label>
                                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="pt-4 flex justify-end gap-3">
                                <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-slate-500 font-bold text-sm hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold text-sm rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">Create Schedule</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Details Modal -->
            <div x-show="viewModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden" @click.away="viewModalOpen = false">
                    <div class="h-32 bg-slate-100 relative">
                        <template x-if="selectedTraining && selectedTraining.course && selectedTraining.course.picture">
                            <img :src="storageUrl + '/' + selectedTraining.course.picture" class="w-full h-full object-cover">
                        </template>
                        <button @click="viewModalOpen = false" class="absolute top-4 right-4 bg-white/90 text-slate-800 p-2 rounded-full shadow-sm hover:bg-white"><i class='bx bx-x text-lg'></i></button>
                    </div>
                    <template x-if="selectedTraining">
                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-2">
                                 <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider rounded-lg" x-text="selectedTraining.org_scope"></span>
                                 <span class="px-2 py-1 bg-purple-50 text-purple-600 text-[10px] font-bold uppercase tracking-wider rounded-lg" x-text="selectedTraining.proficiency"></span>
                            </div>
                            <h3 class="font-bold text-2xl text-slate-800 mb-1" x-text="selectedTraining.title"></h3>
                            <p class="text-slate-500 text-sm font-medium mb-6" x-text="'Course: ' + (selectedTraining.course ? selectedTraining.course.title : 'N/A')"></p>

                            <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Capacity</p>
                                    <p class="font-bold text-slate-700" x-text="selectedTraining.capacity"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Participants</p>
                                    <p class="font-bold text-slate-700" x-text="selectedTraining.participants ? selectedTraining.participants.length : 0"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Duration</p>
                                    <p class="font-bold text-slate-700" x-text="selectedTraining.duration"></p>
                                </div>
                                 <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase">Status</p>
                                    <p class="font-bold text-slate-700 capitalize" x-text="selectedTraining.status ? selectedTraining.status.replace('_', ' ') : ''"></p>
                                </div>
                                <div x-show="selectedTraining.training_type === 'physical' || selectedTraining.training_type === 'both'">
                                    <p class="text-xs font-bold text-slate-400 uppercase">Location</p>
                                    <p class="font-bold text-slate-700" x-text="selectedTraining.location || 'N/A'"></p>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-2">Skills Assessed</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="skill in (selectedTraining.course && selectedTraining.course.competencies ? selectedTraining.course.competencies : [])" :key="skill.id">
                                        <span class="px-2 py-1 border border-slate-200 text-slate-600 text-xs font-bold rounded-lg" x-text="skill.name"></span>
                                    </template>
                                    <template x-if="!selectedTraining.course || !selectedTraining.course.competencies || selectedTraining.course.competencies.length === 0">
                                        <span class="text-slate-400 text-xs italic">No specific skills listed.</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Immediately Start OTP Modal -->
            <div x-show="startModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6" @click.away="startModalOpen = false">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                            <i class='bx bx-error'></i>
                        </div>
                        <h3 class="font-bold text-lg text-slate-800">Start Immediately?</h3>
                        <p class="text-slate-500 text-xs mt-2">This will bypass the scheduled start time. Please enter the authorization code to proceed.</p>
                    </div>
                    
                    <form :action="'{{ url('/training') }}/' + (selectedTraining ? selectedTraining.id : '') + '/start'" method="POST">
                        @csrf
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold text-slate-500 uppercase">Authorization Code</label>
                                <button type="button" @click="sendOtp()" :disabled="otpLoading" class="text-xs font-bold text-amber-500 hover:text-amber-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                    <span x-show="!otpLoading && !otpSent">Send Code to Email</span>
                                    <span x-show="otpLoading">Sending...</span>
                                    <span x-show="!otpLoading && otpSent">Resend Code</span>
                                </button>
                            </div>
                            <input type="text" name="otp" required placeholder="OTP" class="w-full text-center tracking-[1em] font-mono text-lg px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none uppercase">
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="startModalOpen = false" class="flex-1 px-4 py-2.5 text-slate-500 font-bold text-sm bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Cancel</button>
                            <button type="submit" class="flex-1 px-4 py-2.5 bg-amber-500 text-white font-bold text-sm hover:bg-amber-600 rounded-xl shadow-lg shadow-amber-100 transition-colors">Confirm Start</button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>
    
    <!-- Ion Icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
