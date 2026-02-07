<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Succession Planning</title>
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <!-- Bootstrap CSS for Sidebar compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    @include('partials.admin-sidebar')

    <div class="main-content" x-data="{ showModal: false }">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Succession Planning</h1>
            <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Create Plan
            </button>
        </div>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Employee -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Employee</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $totalEmployees }}</h3>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Active Employee -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Employee</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $activeEmployees }}</h3>
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg text-green-600">
                        <i data-lucide="user-check" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Completed Assessments -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Completed Assessments</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $completedAssessments }}</h3>
                    </div>
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Plans -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending Plans</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $pendingPlans }}</h3>
                    </div>
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8">
            
            <!-- Tabs & Tables -->
            <div x-data="{ activeTab: 'available' }">
                
                <!-- Tab Navigation -->
                <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-4 w-fit">
                    <button 
                        @click="activeTab = 'available'" 
                        :class="{ 'bg-white text-gray-900 shadow': activeTab === 'available', 'text-gray-500 hover:text-gray-700': activeTab !== 'available' }"
                        class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                        Available Positions
                    </button>
                    <button 
                        @click="activeTab = 'succession'" 
                        :class="{ 'bg-white text-gray-900 shadow': activeTab === 'succession', 'text-gray-500 hover:text-gray-700': activeTab !== 'succession' }"
                        class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                        Succession Table
                    </button>
                </div>

                <!-- Tab 1: Available Positions -->
                <div x-show="activeTab === 'available'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">Position</th>
                                    <th class="px-6 py-3">Department</th>
                                    <th class="px-6 py-3">Current Employee</th>
                                    <th class="px-6 py-3">Priority</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($availablePositions as $pos)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $pos['position'] }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ $pos['department'] }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ $pos['current_employee'] }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $pos['priority'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right space-x-2">
                                        <button class="text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                        <button class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No positions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Succession Table -->
                <div x-show="activeTab === 'succession'" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">Succession Candidates</th>
                                    <th class="px-6 py-3">Position</th>
                                    <th class="px-6 py-3">Department</th>
                                    <th class="px-6 py-3">Priority</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($successionPlans as $plan)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $plan['candidate'] }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ $plan['position'] }}</td>
                                    <td class="px-6 py-3 text-gray-500">{{ $plan['department'] }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ $plan['priority'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right space-x-2">
                                        <button class="text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                        <button class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No succession plans found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- Create Plan Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Background overlay -->
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-800/50 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="showModal = false"></div>

                <!-- Modal panel -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Create Succession Plan
                            </h3>
                            <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                        
                        <form action="{{ route('succession.plans.store') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <!-- Department -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select name="department_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Succession Employee (Search & Select) -->
                            <div x-data="{ 
                                search: '', 
                                open: false, 
                                selected: [],
                                items: {{ $employees->map(fn($e) => ['id' => $e->id, 'name' => $e->first_name . ' ' . $e->last_name, 'dept' => $e->department, 'position' => $e->jobRole ? $e->jobRole->name : 'N/A'])->toJson() }},
                                get currentPositionText() {
                                    if (this.selected.length === 0) return '';
                                    if (this.selected.length === 1) {
                                        const emp = this.items.find(i => i.id == this.selected[0]);
                                        return emp ? emp.position : '';
                                    }
                                    return 'Multiple Positions';
                                }
                            }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Succession Employee</label>
                                <!-- Hidden Inputs for Form Submission -->
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="employee_ids[]" :value="id">
                                </template>
                                
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        x-model="search"
                                        @focus="open = true"
                                        @click.away="open = false"
                                        placeholder="Search employees..." 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 pl-3"
                                    >
                                    
                                    <!-- Dropdown List -->
                                    <div x-show="open && search.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="item in items.filter(i => i.name.toLowerCase().includes(search.toLowerCase()))" :key="item.id">
                                            <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                                <input type="checkbox" :value="item.id" x-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 mr-2">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900" x-text="item.name"></p>
                                                    <p class="text-xs text-gray-500">
                                                        <span x-text="item.dept"></span> • <span x-text="item.position"></span>
                                                    </p>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                                
                                <!-- Selected Items Chips -->
                                <div class="flex flex-wrap gap-2 mt-2 mb-4">
                                    <template x-for="id in selected" :key="id">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-50 text-indigo-700">
                                            <span x-text="items.find(i => i.id == id)?.name"></span>
                                            <button type="button" @click="selected = selected.filter(i => i != id)" class="ml-1 text-indigo-400 hover:text-indigo-600">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <!-- Current Position (Auto-shown) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Position</label>
                                    <input type="text" readonly :value="currentPositionText" placeholder="Auto-filled based on selection" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 shadow-sm text-sm py-2 cursor-not-allowed">
                                </div>
                            </div>

                            <!-- Succession Position -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Succession Position</label>
                                <select name="target_role_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                                    <option value="">Select Position</option>
                                    @foreach($jobRoles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Readiness Level -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Readiness Level</label>
                                <select name="readiness" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                                    <option value="Ready Now">Ready Now</option>
                                    <option value="Ready in 1-2 Years">Ready in 1-2 Years</option>
                                    <option value="Ready in 3-5 Years">Ready in 3-5 Years</option>
                                </select>
                            </div>
                            
                            <!-- Notes (Optional, if supported by backend) -->
                            <!-- 
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2" rows="3"></textarea>
                            </div>
                            -->

                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                                    Create Plan
                                </button>
                                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
