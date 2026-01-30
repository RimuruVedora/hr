<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduFlow - Course Management</title>
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/dashboard/dashboard.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <!-- Bootstrap CSS for Sidebar compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom Scrollbar for modals */
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb:hover { background: #a0a0a0; }

        .fade-in { animation: fadeIn 0.2s ease-in-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    @include('partials.Employee-sidebar')

    <div class="main-content" style="margin-left: 220px;">
        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6">
            
            <!-- Tab Header -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <a href="#" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        Available Courses
                    </a>
                    <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        My Learning
                    </a>
                </nav>
            </div>

            <!-- Course Grid -->
            <div id="courseGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Courses injected by JS -->
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyState" class="hidden h-64 flex flex-col items-center justify-center text-gray-500">
                <i data-lucide="search-x" class="w-12 h-12 mb-2 text-gray-300"></i>
                <p>No courses found matching your criteria.</p>
            </div>
        </main>
    </div>

    <!-- ================= MODALS ================= -->

    <!-- 1. Course Detail Modal -->
    <div id="courseDetailModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col fade-in">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-100 flex justify-between items-start">
                <div>
                    <span id="detailBadge" class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">Technology</span>
                    <h2 id="detailTitle" class="text-2xl font-bold mt-2 text-gray-900">Course Title</h2>
                </div>
                <button onclick="closeModal('courseDetailModal')" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto modal-scroll space-y-4">
                <p id="detailDesc" class="text-gray-600 leading-relaxed"></p>
                
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Start Date</p>
                        <p id="detailDate" class="font-medium text-gray-900 mt-1"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Duration</p>
                        <p id="detailDuration" class="font-medium text-gray-900 mt-1"></p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Skills Offered</h3>
                    <div id="detailSkills" class="flex flex-wrap gap-2">
                        <!-- Skills injected here -->
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-6 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex flex-col gap-3">
                <button id="enrollActionBtn" class="w-full py-3 px-4 rounded-xl text-white font-semibold shadow-lg transition-transform active:scale-[0.98] flex items-center justify-center gap-2">
                    <!-- Text changes dynamically -->
                </button>
                <button id="examActionBtn" class="hidden w-full py-3 px-4 rounded-xl text-gray-400 font-semibold border border-gray-200 bg-white cursor-not-allowed flex items-center justify-center gap-2 transition-colors select-none" disabled>
                    <i data-lucide="lock" class="w-4 h-4"></i> Take Exam (Locked by Admin)
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-[60] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6 fade-in text-center">
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4 text-yellow-600">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Are you sure?</h3>
            <p id="confirmMessage" class="text-gray-500 text-sm mb-6">Do you want to proceed with this action?</p>
            <div class="flex gap-3">
                <button onclick="closeModal('confirmModal')" class="flex-1 py-2 px-4 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">Cancel</button>
                <button id="confirmYesBtn" class="flex-1 py-2 px-4 bg-indigo-600 rounded-lg text-white font-medium hover:bg-indigo-700 shadow-md">Confirm</button>
            </div>
        </div>
    </div>

    <!-- 3. Success Modal -->
    <div id="successModal" class="fixed inset-0 z-[70] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6 fade-in text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-green-500"></div>
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600">
                <i data-lucide="check-circle-2" class="w-8 h-8"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Success!</h3>
            <p class="text-gray-500 text-sm mb-6">You have successfully enrolled in the course.</p>
            <button onclick="closeModal('successModal')" class="w-full py-2 px-4 bg-green-600 rounded-lg text-white font-medium hover:bg-green-700 shadow-md">
                Continue Learning
            </button>
        </div>
    </div>

    <!-- 4. Learning Materials / View Modal -->
    <div id="materialsModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col fade-in">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-indigo-50 rounded-t-2xl">
                <div>
                    <h2 class="text-xl font-bold text-indigo-900">Learning Dashboard</h2>
                    <p id="materialCourseTitle" class="text-indigo-600 text-sm font-medium"></p>
                </div>
                <button onclick="closeModal('materialsModal')" class="text-indigo-400 hover:text-indigo-600 p-1">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto modal-scroll space-y-8">
                
                <!-- Course Modules / Links -->
                <div class="mb-6">
                    <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <i data-lucide="book-open" class="w-4 h-4 text-indigo-600"></i>
                        Learning Modules
                    </h4>
                    <div class="space-y-3" id="materialLinksContainer">
                        <!-- Links injected here -->
                    </div>
                </div>

                <!-- Exam Information Section -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                    <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <i data-lucide="graduation-cap" class="w-4 h-4 text-indigo-600"></i>
                        Final Exam Information
                    </h4>
                    
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                            <i data-lucide="file-question" class="w-5 h-5 text-indigo-600"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-900" id="examTitle">Final Exam</h5>
                            <p class="text-xs text-gray-500 mt-1" id="examLocText">Online</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-sm text-gray-600 bg-white p-3 rounded-lg border border-gray-100" id="examItems">
                         <!-- Items injected here -->
                    </ul>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl text-right">
                <button onclick="closeModal('materialsModal')" class="text-gray-500 hover:text-gray-700 font-medium text-sm px-4 py-2">Close Dashboard</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        // --- Data ---
        const courses = @json($courses);

        // --- State ---
        let currentCourseId = null;
        let pendingAction = null; // 'enroll' or 'material'
        let currentMaterialName = "";

        // --- DOM Elements ---
        const grid = document.getElementById('courseGrid');
        const emptyState = document.getElementById('emptyState');
        const searchInput = document.getElementById('searchInput');

        // --- Initialization ---
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            renderCourses(courses);
        });

        // --- Rendering ---
        function renderCourses(data) {
            grid.innerHTML = '';
            
            if(data.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            } else {
                emptyState.classList.add('hidden');
            }

            data.forEach(course => {
                const card = document.createElement('div');
                card.className = "bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all cursor-pointer group flex flex-col h-full";
                card.onclick = () => openDetailModal(course.id);

                // Dynamic Enroll Button text for card preview
                let btnText = "Not Scheduled";
                let btnClass = "bg-gray-100 text-gray-500 border border-gray-200 cursor-not-allowed";

                if (course.enrolled) {
                    btnText = "View Learning Material";
                    btnClass = "bg-green-50 text-green-700 border border-green-200 hover:bg-green-100";
                } else if (course.has_schedule) {
                    if (course.is_full) {
                        btnText = "Class Full";
                        btnClass = "bg-red-50 text-red-600 border border-red-200 cursor-not-allowed";
                    } else {
                        btnText = "Enroll Now";
                        btnClass = "bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100";
                    }
                }

                card.innerHTML = `
                    <div class="h-32 bg-gradient-to-r from-slate-100 to-gray-200 relative">
                        <span class="absolute top-4 left-4 bg-white/90 backdrop-blur text-xs font-bold px-2 py-1 rounded text-gray-700 shadow-sm">
                            ${course.category}
                        </span>
                        ${course.enrolled ? '<span class="absolute top-4 right-4 bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded shadow-sm">Enrolled</span>' : ''}
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-1">${course.title}</h3>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2 flex-1">${course.description}</p>
                        
                        <div class="flex items-center gap-4 text-xs text-gray-500 mb-4 border-t pt-4 border-gray-100">
                            <div class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i> ${course.date}
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> ${course.duration}
                            </div>
                        </div>

                        <div class="mt-auto">
                            <span class="w-full py-2 px-3 rounded-lg text-sm font-semibold text-center block transition-colors ${btnClass}">
                                ${btnText}
                            </span>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
            lucide.createIcons();
        }

        // --- Modal Logic ---

        function openDetailModal(id) {
            currentCourseId = id;
            const course = courses.find(c => c.id === id);
            
            // Populate Details
            document.getElementById('detailTitle').textContent = course.title;
            document.getElementById('detailDesc').textContent = course.description;
            document.getElementById('detailDate').textContent = course.date;
            document.getElementById('detailDuration').textContent = course.duration;
            document.getElementById('detailBadge').textContent = course.category;

            // Populate Skills
            const skillsContainer = document.getElementById('detailSkills');
            skillsContainer.innerHTML = course.skills.map(skill => 
                `<span class="px-3 py-1 bg-gray-100 text-gray-600 text-sm rounded-full border border-gray-200">${skill}</span>`
            ).join('');

            // Configure Main Action Button
            const btn = document.getElementById('enrollActionBtn');
            updateDetailButtonState(course, btn);

            toggleModal('courseDetailModal', true);
        }

        function updateDetailButtonState(course, btn) {
            const examBtn = document.getElementById('examActionBtn');

            // Remove all possible classes first
            btn.className = "w-full py-3 px-4 rounded-xl font-semibold shadow-lg transition-transform active:scale-[0.98] flex items-center justify-center gap-2";

            if (course.enrolled) {
                // View Mode
                btn.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
                btn.innerHTML = `<i data-lucide="eye" class="w-5 h-5"></i> View Learning Material`;
                btn.onclick = () => openMaterialsModal(course);
                
                // Show Exam Button
                if(examBtn) {
                    examBtn.classList.remove('hidden');
                    if (course.exam_access) {
                        examBtn.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed', 'bg-white');
                        examBtn.classList.add('bg-indigo-600', 'text-white', 'hover:bg-indigo-700', 'shadow-md', 'cursor-pointer');
                        examBtn.disabled = false;
                        examBtn.innerHTML = `<i data-lucide="pen-tool" class="w-4 h-4"></i> Take Final Exam`;
                        examBtn.onclick = () => {
                            if (course.training_id) {
                                window.location.href = examStartRouteTemplate.replace('___ID___', course.training_id);
                            } else {
                                alert('Training ID not found.');
                            }
                        };
                    } else {
                        examBtn.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                        examBtn.classList.remove('bg-indigo-600', 'text-white', 'hover:bg-indigo-700', 'shadow-md');
                        examBtn.disabled = true;
                        
                        let msg = "Exam Locked";
                        if (course.training_status === 'published') {
                            msg = "Exam Locked (Check Schedule)";
                        } else if (course.training_status) {
                             msg = `Exam Locked (${course.training_status})`;
                        }
                        
                        examBtn.innerHTML = `<i data-lucide="lock" class="w-4 h-4"></i> ${msg}`;
                    }
                }
            } else {
                // Not Enrolled
                if (course.has_schedule) {
                    if (course.is_full) {
                        // Class Full
                        btn.classList.add('bg-red-50', 'text-red-600', 'cursor-not-allowed', 'border', 'border-red-200');
                        btn.innerHTML = `Class Full`;
                        btn.onclick = null;
                    } else {
                        // Available to Enroll
                        btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'text-white');
                        btn.innerHTML = `Enroll Now`;
                        btn.onclick = () => initiateEnrollment(course);
                    }
                } else {
                    // Not Scheduled
                    btn.classList.add('bg-gray-400', 'cursor-not-allowed', 'text-white');
                    btn.innerHTML = `Not Scheduled`;
                    btn.onclick = null;
                }

                // Hide Exam Button
                if(examBtn) examBtn.classList.add('hidden');
            }
            lucide.createIcons();
        }

        // --- Enroll Flow ---
        
        let targetCourseForEnrollment = null;
        // Generate a base route string, replacing the placeholder ID with a dummy value we can swap out
        const enrollRouteTemplate = "{{ route('training.enroll', ['id' => '___ID___']) }}";
        const examStartRouteTemplate = "{{ route('exam.start', ['trainingId' => '___ID___']) }}";

        function initiateEnrollment(course) {
            pendingAction = 'enroll';
            targetCourseForEnrollment = course;
            toggleModal('courseDetailModal', false);
            setTimeout(() => {
                document.getElementById('confirmMessage').textContent = `You are about to enroll in "${course.title}". Confirm?`;
                toggleModal('confirmModal', true);
            }, 200);
        }

        // --- Materials Modal ---
        function openMaterialsModal(course) {
            toggleModal('courseDetailModal', false);
            
            document.getElementById('materialCourseTitle').textContent = course.title;
            document.getElementById('examTitle').textContent = course.exam.title || 'Final Exam';
            document.getElementById('examLocText').textContent = course.exam.type || 'Online';

            // Render Links
            const linksContainer = document.getElementById('materialLinksContainer');
            linksContainer.innerHTML = course.materials.map(m => `
                <a href="${m.link}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-indigo-50 hover:border-indigo-200 transition-colors group">
                    <div class="w-8 h-8 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-200">
                        <i data-lucide="file" class="w-4 h-4"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">${m.title}</span>
                    <i data-lucide="external-link" class="w-4 h-4 text-gray-400 ml-auto group-hover:text-indigo-400"></i>
                </a>
            `).join('');

            // Render Exam Items/Details
            const itemsList = document.getElementById('examItems');
            // We now have dynamic data for items count
            itemsList.innerHTML = `
                <li class="flex justify-between items-center border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                    <span class="font-medium text-gray-700">Number of Items</span>
                    <span class="font-bold text-indigo-600">${course.exam.items}</span>
                </li>
                <li class="flex justify-between items-center border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                    <span class="font-medium text-gray-700">Duration</span>
                    <span>${course.exam.duration}</span>
                </li>
                <li class="flex justify-between items-center border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                    <span class="font-medium text-gray-700">Training Type</span>
                    <span>${course.exam.type || 'Online'}</span>
                </li>
            `;

            setTimeout(() => {
                toggleModal('materialsModal', true);
            }, 200);
            
            lucide.createIcons();
        }

        // --- Confirmation Handling ---

        document.getElementById('confirmYesBtn').onclick = () => {
            const btn = document.getElementById('confirmYesBtn');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = "Processing...";

            if (pendingAction === 'enroll' && targetCourseForEnrollment) {
                const trainingId = targetCourseForEnrollment.available_training_id;
                const url = enrollRouteTemplate.replace('___ID___', trainingId);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    toggleModal('confirmModal', false);
                    btn.disabled = false;
                    btn.innerText = originalText;

                    if (data.success) {
                        // 1. Update the specific course object in our global array
                        if (targetCourseForEnrollment) {
                            targetCourseForEnrollment.enrolled = true;
                            
                            // Also find by index to be absolutely sure we update the array reference
                            const idx = courses.findIndex(c => c.id == targetCourseForEnrollment.id);
                            if (idx !== -1) {
                                courses[idx].enrolled = true;
                                // Assign the training ID from the available training (since we just enrolled)
                                courses[idx].training_id = courses[idx].available_training_id;
                                
                                // Enable exam access if applicable (simplified logic for immediate feedback)
                                if (courses[idx].training_status === 'published') {
                                    courses[idx].exam_access = true;
                                }
                            }
                        }

                        // 2. Re-render the dashboard grid to show "View Learning Material"
                        renderCourses(courses);

                        // 3. Update the modal button state if the modal happens to be open (it shouldn't be, but for safety)
                        const btn = document.getElementById('enrollActionBtn');
                        if (btn && targetCourseForEnrollment) {
                            updateDetailButtonState(targetCourseForEnrollment, btn);
                        }
                        
                        // 4. Show Success Message
                        toggleModal('successModal', true);
                        
                        // 5. Configure "Continue Learning" to open the Materials Modal
                        document.querySelector('#successModal button').onclick = () => {
                             toggleModal('successModal', false);
                             if (targetCourseForEnrollment) {
                                 openMaterialsModal(targetCourseForEnrollment);
                             }
                        };
                    } else {
                        alert(data.message || "Enrollment failed.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    toggleModal('confirmModal', false);
                    btn.disabled = false;
                    btn.innerText = originalText;
                    alert("An error occurred. Please try again.");
                });
            } else {
                toggleModal('confirmModal', false);
                btn.disabled = false;
                btn.innerText = originalText;
            }
        };

        // --- Utilities ---

        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (show) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function closeModal(modalId) {
            toggleModal(modalId, false);
        }
    </script>
</body>
</html>