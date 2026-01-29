<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HR Learn | Courses</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        window.pdfjsLib = window['pdfjs-dist/build/pdf'];
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        window.appUrl = "{{ config('app.url') }}";
        window.initialCourses = @json($courses);
        window.competenciesMaster = @json($competenciesMaster);
    </script>
    @vite(['resources/css/learning/learning_exam_creation.css', 'resources/js/learning/learning_exam_creation.js'])
</head>
<body class="min-h-screen bg-gray-50">

    <!-- Sidebar -->
    @include('partials.admin-sidebar')

    <!-- Main Content -->
    <div class="ml-[240px] transition-all duration-300">
        <main class="max-w-7xl mx-auto px-8 py-12">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-800">Courses</h2>
                <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition shadow-lg">
                    <i class="fas fa-plus-circle"></i>
                    Create Course
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200 mb-8">
                <button onclick="setTab('published')" id="tab-published" class="px-6 py-3 font-medium text-sm transition-colors border-b-2 border-blue-600 text-blue-600">
                    Published
                </button>
                <button onclick="setTab('draft')" id="tab-draft" class="px-6 py-3 font-medium text-sm transition-colors border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                    Drafts
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 mb-8">
                <div class="relative flex-grow max-w-sm">
                    <input type="text" placeholder="Search courses..." class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button class="p-2.5 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-search text-gray-400"></i>
                </button>
            </div>

            <!-- Course Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="courseGrid"></div>
        </main>
    </div>

    <!-- Create Course Modal -->
    <div id="createModal" class="fixed inset-0 z-40 hidden flex items-center justify-center p-4 modal-overlay">
        <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-xl font-bold text-gray-800">Create New Course</h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="createCourseForm" class="overflow-y-auto px-8 py-6 space-y-6 custom-scrollbar">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Course Title</label>
                    <input type="text" id="courseTitle" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Advanced Leadership Skills">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Level Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Level</label>
                        <select id="courseLevel" onchange="toggleDeptDropdown()" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Level</option>
                            <option value="organization">Organization Wide</option>
                            <option value="department">Department</option>
                            <option value="management">Management</option>
                            <option value="team">Team</option>
                        </select>
                    </div>

                    <!-- Department (Conditional) -->
                    <div id="deptDropdownContainer" class="hidden">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Target Department</label>
                        <select id="courseDept" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <input type="text" id="courseCat" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Compliance">
                    </div>
                </div>

                <!-- Competencies -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Competencies</label>
                    <div class="relative">
                        <div id="selectedCompetencies" class="flex flex-wrap gap-2 mb-2"></div>
                        <input type="text" id="compSearch" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Search competencies...">
                        <div id="compDropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-40 overflow-y-auto"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Picture Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Course Picture</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg h-32 relative hover:border-blue-500 transition overflow-hidden group">
                            <input type="file" id="coursePic" class="hidden" accept="image/*">
                            <label for="coursePic" id="coursePicLabel" class="cursor-pointer absolute inset-0 flex flex-col items-center justify-center bg-white">
                                <i class="fas fa-image text-gray-400 text-2xl mb-2"></i>
                                <p class="text-xs text-gray-500">Click to upload JPG, PNG</p>
                            </label>
                            <img id="coursePicPreview" class="absolute inset-0 w-full h-full object-cover hidden">
                            <button type="button" id="removePicBtn" onclick="removeCoursePic(event)" class="hidden absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 items-center justify-center hover:bg-red-600 shadow-md transition-transform transform hover:scale-110">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <!-- PDF Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Course Material (PDF)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 cursor-pointer transition">
                            <input type="file" id="coursePdf" class="hidden" accept=".pdf">
                            <label for="coursePdf" class="cursor-pointer">
                                <i class="fas fa-file-pdf text-gray-400 text-2xl mb-2"></i>
                                <p class="text-xs text-gray-500">Click to upload PDF</p>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Duration</label>
                        <input type="text" id="courseDur" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. 2 hours">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="courseDesc" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter course details..."></textarea>
                </div>
            </form>

            <div class="px-8 py-6 border-t border-gray-100 bg-gray-50 flex gap-3">
                <button onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 font-medium">Cancel</button>
                <button onclick="handleCreateSubmit()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Save as Draft</button>
            </div>
        </div>
    </div>

    <!-- View Course Modal -->
    <div id="viewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 modal-overlay">
        <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
            <!-- Modal Header -->
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-xl font-bold text-gray-800" id="viewCourseTitle">Course Title</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="overflow-y-auto px-8 py-8 space-y-8 custom-scrollbar bg-white">
                
                <!-- Top Section: Image & Basic Info -->
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Course Image -->
                    <div class="w-full md:w-1/3">
                        <div class="aspect-video rounded-xl overflow-hidden shadow-lg border border-gray-100 bg-gray-100 flex items-center justify-center">
                            <img id="viewCoursePic" class="w-full h-full object-cover hidden" alt="Course Image">
                            <i class="fas fa-image text-4xl text-gray-300"></i>
                        </div>
                    </div>
                    
                    <!-- Details -->
                    <div class="w-full md:w-2/3 space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <span id="viewCourseCategory" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">Category</span>
                            <span id="viewCourseLevel" class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">Level</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="far fa-clock text-blue-500"></i>
                                <span id="viewCourseDuration">Duration</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="far fa-building text-blue-500"></i>
                                <span id="viewCourseDept">Department</span>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Description</h4>
                            <p id="viewCourseDesc" class="text-gray-600 text-sm leading-relaxed"></p>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Competencies</h4>
                            <div id="viewCourseComps" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>

                <!-- PDF Content Section -->
                <div id="pdfSection" class="border-t border-gray-100 pt-8 hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-file-pdf text-red-500"></i> Course Material
                        </h4>
                        <a id="downloadPdfBtn" href="#" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                            Open Original PDF <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 shadow-inner max-h-[400px] overflow-y-auto relative">
                        <div id="pdfLoading" class="absolute inset-0 bg-gray-50 bg-opacity-90 flex flex-col items-center justify-center z-10 hidden">
                            <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-2"></i>
                            <span class="text-sm text-gray-600 font-medium">Extracting text from PDF...</span>
                        </div>
                        <pre id="pdfTextContent" class="whitespace-pre-wrap font-mono text-sm text-gray-700 leading-relaxed"></pre>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-5 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button onclick="closeViewModal()" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">Close</button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 modal-overlay">
        <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-2xl">
            <div id="modalIcon" class="w-12 h-12 rounded-full flex items-center justify-center mb-4 mx-auto"></div>
            <h3 id="modalTitle" class="text-xl font-bold text-center text-gray-800 mb-2"></h3>
            <p id="modalDescription" class="text-gray-600 text-center mb-6 text-sm"></p>
            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">Cancel</button>
                <button id="modalConfirmBtn" class="flex-1 px-4 py-2 rounded-lg text-white font-medium transition">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="successToast" class="fixed bottom-8 right-8 z-50 hidden transform transition-all">
        <div class="bg-green-600 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <div>
                <p class="font-bold">Success!</p>
                <p class="text-sm opacity-90" id="successMsg"></p>
            </div>
        </div>
    </div>
</body>
</html>