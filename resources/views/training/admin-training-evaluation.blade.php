<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Evaluation</title>
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
        <main class="max-w-7xl mx-auto p-4 md:p-8" x-data="evaluationHandler()">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Training Evaluation</h1>
                <p class="text-slate-500 mt-1">Select an ongoing training to evaluate participants.</p>
            </div>

            <!-- Selection Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
                <div class="max-w-md">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Select Ongoing Training</label>
                    <select x-model="selectedTrainingId" @change="fetchParticipants()" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">-- Select Training --</option>
                        @foreach($trainings as $training)
                            <option value="{{ $training->id }}">{{ $training->title }} ({{ $training->participants_count }} Participants)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Participants Table -->
            <div x-show="selectedTrainingId && !loading" x-cloak x-transition class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Participants List</h3>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-blue-50 text-blue-600" x-text="participants.length + ' Employees'"></span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Employee</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Department</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Grade / Score</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Remarks</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="p in participants" :key="p.id">
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-800" x-text="p.employee_name"></div>
                                        <div class="text-xs text-slate-400" x-text="p.employee_id"></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600" x-text="p.department"></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-600 capitalize" x-text="p.status"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" x-model="p.grade" class="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded text-sm focus:outline-none focus:border-blue-500 transition-colors" placeholder="Grade">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" x-model="p.remarks" class="w-full min-w-[200px] px-3 py-1.5 bg-white border border-slate-200 rounded text-sm focus:outline-none focus:border-blue-500 transition-colors" placeholder="Optional remarks...">
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="saveGrade(p)" 
                                                :class="p.saving ? 'bg-slate-100 text-slate-400 cursor-wait' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-200'"
                                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all"
                                                :disabled="p.saving">
                                            <span x-show="!p.saving">Save</span>
                                            <span x-show="p.saving">...</span>
                                        </button>
                                        <div x-show="p.saved" x-transition.opacity.duration.1000ms class="text-[10px] text-emerald-600 font-medium mt-1">Saved!</div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="participants.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                    No participants found for this training.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="text-center py-12">
                <i class='bx bx-loader-alt bx-spin text-3xl text-blue-600'></i>
                <p class="text-slate-500 mt-2">Loading participants...</p>
            </div>

        </main>
    </div>

    <script>
        function evaluationHandler() {
            return {
                selectedTrainingId: '',
                participants: [],
                loading: false,

                async fetchParticipants() {
                    if (!this.selectedTrainingId) {
                        this.participants = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(`{{ url('training') }}/${this.selectedTrainingId}/participants`);
                        const data = await response.json();
                        this.participants = data.map(p => ({...p, saving: false, saved: false}));
                    } catch (error) {
                        console.error('Error fetching participants:', error);
                        alert('Failed to load participants.');
                    } finally {
                        this.loading = false;
                    }
                },

                async saveGrade(participant) {
                    participant.saving = true;
                    participant.saved = false;
                    try {
                        const response = await fetch('{{ route('training.grade.update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                participant_id: participant.id,
                                grade: participant.grade,
                                remarks: participant.remarks
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            participant.saved = true;
                            setTimeout(() => { participant.saved = false; }, 2000);
                        } else {
                            alert(result.message || 'Failed to save.');
                        }
                    } catch (error) {
                        console.error('Error saving grade:', error);
                        alert('An error occurred while saving.');
                    } finally {
                        participant.saving = false;
                    }
                }
            }
        }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>