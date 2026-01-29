let currentTab = 'published';
let courses = [];
let competenciesMaster = [];
let selectedComps = [];
let csrfToken = '';
let appUrl = '/hr/public'; // Fallback

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize data from window
    const compsMap = window.competenciesMaster || {}; 
    competenciesMaster = Object.values(compsMap);
    courses = window.initialCourses || [];
    if (window.appUrl) {
        appUrl = window.appUrl.replace(/\/$/, ''); // Remove trailing slash
    }

    // Get CSRF Token
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        csrfToken = meta.getAttribute('content');
    } else {
        console.error('CSRF token meta tag not found');
    }

    // Image Preview Logic
    const coursePicInput = document.getElementById('coursePic');
    const coursePicPreview = document.getElementById('coursePicPreview');
    const coursePicLabel = document.getElementById('coursePicLabel');
    const removePicBtn = document.getElementById('removePicBtn');

    if (coursePicInput) {
        coursePicInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (coursePicPreview) {
                        coursePicPreview.src = e.target.result;
                        coursePicPreview.classList.remove('hidden');
                    }
                    if (coursePicLabel) coursePicLabel.classList.add('hidden');
                    if (removePicBtn) {
                        removePicBtn.classList.remove('hidden');
                        removePicBtn.classList.add('flex');
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Competency Search Logic
    const compSearch = document.getElementById('compSearch');
    const compDropdown = document.getElementById('compDropdown');

    if (compSearch) {
        compSearch.addEventListener('focus', () => showCompetencyDropdown(compSearch.value));
        compSearch.addEventListener('input', (e) => showCompetencyDropdown(e.target.value));
    }

    document.addEventListener('click', (e) => {
        // Keep open if clicking inside search or dropdown
        if (compSearch && compDropdown && !e.target.closest('#compSearch') && !e.target.closest('#compDropdown')) {
            compDropdown.classList.add('hidden');
        }
    });

    // Initial Render
    renderCourses();
});

// Expose functions to window
window.toggleDeptDropdown = toggleDeptDropdown;
window.toggleCompetency = toggleCompetency;
window.removeCompetency = removeCompetency;
window.openCreateModal = openCreateModal;
window.closeCreateModal = closeCreateModal;
window.handleCreateSubmit = handleCreateSubmit;
window.setTab = setTab;
window.closeConfirmModal = closeConfirmModal;
window.approveDraft = approveDraft;
window.deleteCourse = deleteCourse;
window.removeCoursePic = removeCoursePic;
window.viewCourse = viewCourse;
window.closeViewModal = closeViewModal;

// --- Functions ---

function viewCourse(id) {
    const course = courses.find(c => c.id === id);
    if (!course) return;

    // Helper to safely set text
    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.innerText = text;
    };

    setText('viewCourseTitle', course.title);
    setText('viewCourseCategory', course.category);
    setText('viewCourseLevel', course.level);
    setText('viewCourseDuration', course.duration);
    setText('viewCourseDesc', course.description);
    
    // Department
    const dept = course.department ? course.department.name : 'All Departments';
    setText('viewCourseDept', dept);

    // Picture
    const pic = document.getElementById('viewCoursePic');
    if (pic) {
        if (course.picture) {
            pic.src = `${appUrl}/storage/${course.picture}`;
            pic.classList.remove('hidden');
        } else {
            pic.classList.add('hidden');
        }
    }

    // Competencies
    const compsContainer = document.getElementById('viewCourseComps');
    if (compsContainer) {
        if (course.competencies && course.competencies.length > 0) {
            compsContainer.innerHTML = course.competencies.map(c => 
                `<span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold">${c.name}</span>`
            ).join('');
        } else {
            compsContainer.innerHTML = '<span class="text-gray-400 italic">No competencies assigned</span>';
        }
    }

    // PDF Text Extraction
    const pdfSection = document.getElementById('pdfSection');
    const pdfTextContainer = document.getElementById('pdfTextContent');
    const pdfLoading = document.getElementById('pdfLoading');
    const downloadBtn = document.getElementById('downloadPdfBtn');

    if (pdfSection && course.material_pdf) {
        pdfSection.classList.remove('hidden');
        if (pdfTextContainer) pdfTextContainer.innerHTML = ''; // Clear previous text
        if (pdfLoading) pdfLoading.classList.remove('hidden');
        
        const pdfUrl = `${appUrl}/storage/${course.material_pdf}`;
        if (downloadBtn) downloadBtn.href = pdfUrl;

        // Use PDF.js to extract text
        if (window.pdfjsLib) {
            window.pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
                let fullText = '';
                const totalPages = pdf.numPages;
                let pagesLoaded = 0;

                for (let i = 1; i <= totalPages; i++) {
                    pdf.getPage(i).then(page => {
                        page.getTextContent().then(textContent => {
                            const pageText = textContent.items.map(item => item.str).join(' ');
                            fullText += `--- Page ${i} ---\n\n${pageText}\n\n`;
                            pagesLoaded++;

                            if (pagesLoaded === totalPages) {
                                if (pdfLoading) pdfLoading.classList.add('hidden');
                                // Simple cleanup of extra spaces
                                if (pdfTextContainer) pdfTextContainer.innerText = fullText.replace(/\s+/g, ' ').replace(/--- Page/g, '\n\n--- Page');
                            }
                        });
                    });
                }
            }).catch(err => {
                console.error(err);
                if (pdfLoading) pdfLoading.classList.add('hidden');
                if (pdfTextContainer) pdfTextContainer.innerHTML = '<div class="text-red-500 italic">Failed to load PDF text. Please use the "Open Original PDF" link.</div>';
            });
        } else {
            console.error('PDF.js lib not loaded');
        }

    } else if (pdfSection) {
        pdfSection.classList.add('hidden');
    }

    const viewModal = document.getElementById('viewModal');
    if (viewModal) viewModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeViewModal() {
    const viewModal = document.getElementById('viewModal');
    if (viewModal) viewModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function removeCoursePic(e) {
    if(e) e.preventDefault();
    const coursePicInput = document.getElementById('coursePic');
    const coursePicPreview = document.getElementById('coursePicPreview');
    const coursePicLabel = document.getElementById('coursePicLabel');
    const removePicBtn = document.getElementById('removePicBtn');

    if (coursePicInput) coursePicInput.value = '';
    if (coursePicPreview) {
        coursePicPreview.src = '';
        coursePicPreview.classList.add('hidden');
    }
    if (coursePicLabel) coursePicLabel.classList.remove('hidden');
    if (removePicBtn) {
        removePicBtn.classList.add('hidden');
        removePicBtn.classList.remove('flex');
    }
}

function toggleDeptDropdown() {
    const levelEl = document.getElementById('courseLevel');
    const container = document.getElementById('deptDropdownContainer');
    const deptEl = document.getElementById('courseDept');
    
    if (!levelEl || !container || !deptEl) return;

    const level = levelEl.value;
    if (level === 'department') {
        container.classList.remove('hidden');
        deptEl.setAttribute('required', 'true');
    } else {
        container.classList.add('hidden');
        deptEl.removeAttribute('required');
    }
}

function showCompetencyDropdown(filter = '') {
    const compDropdown = document.getElementById('compDropdown');
    if (!compDropdown) return;

    const list = competenciesMaster.filter(c => 
        c.toLowerCase().includes(filter.toLowerCase())
    );
    
    compDropdown.classList.remove('hidden');

    if (list.length > 0) {
        compDropdown.innerHTML = list.map(c => {
            const isSelected = selectedComps.includes(c);
            return `
            <div onclick="toggleCompetency('${c}')" class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm text-gray-700 border-b border-gray-50 last:border-0 flex items-center gap-3">
                <div class="w-4 h-4 border ${isSelected ? 'border-blue-600 bg-blue-600' : 'border-gray-300 bg-white'} rounded flex items-center justify-center transition-colors">
                    ${isSelected ? '<i class="fas fa-check text-white text-[10px]"></i>' : ''}
                </div>
                <span class="${isSelected ? 'text-blue-700 font-medium' : ''}">${c}</span>
            </div>`;
        }).join('');
    } else {
        compDropdown.innerHTML = `<div class="px-4 py-3 text-sm text-gray-500 text-center">No competencies found matching "${filter}"</div>`;
    }
}

function toggleCompetency(comp) {
    if (selectedComps.includes(comp)) {
        selectedComps = selectedComps.filter(c => c !== comp);
    } else {
        selectedComps.push(comp);
    }
    const compSearch = document.getElementById('compSearch');
    if (compSearch) {
        showCompetencyDropdown(compSearch.value);
        compSearch.focus();
    }
    renderCompetencies();
}

function removeCompetency(comp) {
    selectedComps = selectedComps.filter(c => c !== comp);
    renderCompetencies();
    const compSearch = document.getElementById('compSearch');
    const compDropdown = document.getElementById('compDropdown');
    if (compSearch && compDropdown && !compDropdown.classList.contains('hidden')) {
        showCompetencyDropdown(compSearch.value);
    }
}

function renderCompetencies() {
    const selectedContainer = document.getElementById('selectedCompetencies');
    if (selectedContainer) {
        selectedContainer.innerHTML = selectedComps.map(c => `<span class="competency-tag bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-2">${c}<i onclick="removeCompetency('${c}')" class="fas fa-times cursor-pointer hover:text-blue-900"></i></span>`).join('');
    }
}

function openCreateModal() {
    const modal = document.getElementById('createModal');
    if (modal) modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    const modal = document.getElementById('createModal');
    if (modal) modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    const form = document.getElementById('createCourseForm');
    if (form) form.reset();
    
    const deptContainer = document.getElementById('deptDropdownContainer');
    if (deptContainer) deptContainer.classList.add('hidden');
    
    selectedComps = [];
    renderCompetencies();
}

function handleFetchResponse(response) {
    if (!response.ok) {
        return response.text().then(text => {
            throw new Error(`Server Error: ${response.status} ${response.statusText}`);
        });
    }
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.indexOf("application/json") !== -1) {
        return response.json();
    } else {
        return response.text().then(text => {
            console.error('Expected JSON, got:', text.substring(0, 100));
            throw new Error("Received non-JSON response from server");
        });
    }
}

function handleCreateSubmit() {
    const titleEl = document.getElementById('courseTitle');
    if (!titleEl || !titleEl.value) return;
    const title = titleEl.value;
    
    showConfirmModal({
        title: 'Confirm Save',
        desc: 'Do you want to save this course as a draft?',
        icon: 'fas fa-save',
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600',
        btnBg: 'bg-blue-600 hover:bg-blue-700',
        onConfirm: () => {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('level', document.getElementById('courseLevel').value);
            const deptId = document.getElementById('courseDept').value;
            if (deptId) formData.append('department_id', deptId);
            formData.append('category', document.getElementById('courseCat').value);
            formData.append('duration', document.getElementById('courseDur').value);
            formData.append('description', document.getElementById('courseDesc').value);
            
            selectedComps.forEach(comp => formData.append('competencies[]', comp));
            
            const pic = document.getElementById('coursePic').files[0];
            if (pic) formData.append('picture', pic);
            const pdf = document.getElementById('coursePdf').files[0];
            if (pdf) formData.append('material_pdf', pdf);

            fetch(`${appUrl}/learning/courses`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(handleFetchResponse)
            .then(data => {
                if (data.success) {
                    courses.unshift(data.course); 
                    closeConfirmModal();
                    closeCreateModal();
                    setTab('draft');
                    showToast('Draft created successfully!');
                    renderCourses();
                } else {
                    alert('Error creating course: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }
    });
}

function showConfirmModal(config) {
    const modal = document.getElementById('confirmModal');
    if (!modal) return;
    
    document.getElementById('modalTitle').innerText = config.title;
    document.getElementById('modalDescription').innerText = config.desc;
    
    const icon = document.getElementById('modalIcon');
    icon.className = `w-12 h-12 rounded-full flex items-center justify-center mb-4 mx-auto ${config.iconBg}`;
    icon.innerHTML = `<i class="${config.icon} text-xl ${config.iconColor}"></i>`;
    
    const btn = document.getElementById('modalConfirmBtn');
    btn.className = `flex-1 px-4 py-2 rounded-lg text-white font-medium transition ${config.btnBg}`;
    btn.onclick = config.onConfirm;
    
    modal.classList.remove('hidden');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) modal.classList.add('hidden');
}

function showToast(msg) {
    const toast = document.getElementById('successToast');
    const msgEl = document.getElementById('successMsg');
    if (toast && msgEl) {
        msgEl.innerText = msg;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }
}

function setTab(tab) {
    currentTab = tab;
    const pubTab = document.getElementById('tab-published');
    const dftTab = document.getElementById('tab-draft');
    
    if (pubTab) pubTab.className = tab === 'published' ? "px-6 py-3 font-medium text-sm transition-colors border-b-2 border-blue-600 text-blue-600" : "px-6 py-3 font-medium text-sm transition-colors border-b-2 border-transparent text-gray-500 hover:text-gray-700";
    if (dftTab) dftTab.className = tab === 'draft' ? "px-6 py-3 font-medium text-sm transition-colors border-b-2 border-blue-600 text-blue-600" : "px-6 py-3 font-medium text-sm transition-colors border-b-2 border-transparent text-gray-500 hover:text-gray-700";
    
    renderCourses();
}

function renderCourses() {
    const grid = document.getElementById('courseGrid');
    if (!grid) return;
    
    const filtered = courses.filter(c => c.status === currentTab);
    
    if (filtered.length === 0) {
        grid.innerHTML = '<div class="col-span-3 text-center text-gray-500 py-12">No courses found in this category.</div>';
        return;
    }

    grid.innerHTML = filtered.map(course => `
        <div class="bg-white rounded-2xl overflow-hidden card-shadow border border-gray-100 flex flex-col h-full transition-transform hover:scale-[1.01]">
            ${course.picture ? `<div class="h-48 w-full overflow-hidden"><img src="${appUrl}/storage/${course.picture}" alt="${course.title}" class="w-full h-full object-cover"></div>` : ''}
            <div class="p-6 flex-grow">
                <span class="inline-block bg-blue-600 text-white text-[10px] px-2 py-1 rounded-md font-bold mb-3 uppercase tracking-wider">${course.category}</span>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">${course.title}</h3>
                <div class="flex items-center gap-8 text-gray-500 text-sm mt-4">
                    <span class="flex items-center gap-2"><i class="far fa-clock"></i>${course.duration}</span>
                    <span class="flex items-center gap-2"><i class="fas fa-book-open"></i>1 module</span>
                </div>
            </div>
            <div class="p-6 pt-0 flex gap-2">
                ${course.status === 'published' ? 
                    `<button class="flex-grow px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 font-medium text-sm">Edit Course</button>
                     <button onclick="deleteCourse(${course.id})" class="p-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 transition"><i class="far fa-trash-alt"></i></button>` :
                    `<div class="flex flex-col gap-2 w-full">
                        <button onclick="viewCourse(${course.id})" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm transition"><i class="far fa-eye mr-2"></i>View Details</button>
                        <div class="flex gap-2">
                            <button onclick="approveDraft(${course.id})" class="flex-grow px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium text-sm">Approve</button>
                            <button onclick="deleteCourse(${course.id})" class="flex-grow px-4 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 transition font-medium text-sm">Reject</button>
                        </div>
                     </div>`
                }
            </div>
        </div>
    `).join('');
}

function approveDraft(id) {
    showConfirmModal({
        title: 'Approve Course',
        desc: 'Publish this course to the main catalog?',
        icon: 'fas fa-check',
        iconBg: 'bg-green-100',
        iconColor: 'text-green-600',
        btnBg: 'bg-green-600 hover:bg-green-700',
        onConfirm: () => {
            fetch(`${appUrl}/learning/courses/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: 'published' })
            })
            .then(handleFetchResponse)
            .then(data => {
                if (data.success) {
                    const c = courses.find(x => x.id === id);
                    if(c) c.status = 'published';
                    closeConfirmModal();
                    setTab('published');
                    showToast('Course published successfully!');
                } else {
                     alert('Error publishing course: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }
    });
}

function deleteCourse(id) {
    showConfirmModal({
        title: 'Delete Course',
        desc: 'Are you sure you want to delete this course?',
        icon: 'far fa-trash-alt',
        iconBg: 'bg-red-100',
        iconColor: 'text-red-600',
        btnBg: 'bg-red-600 hover:bg-red-700',
        onConfirm: () => {
            fetch(`${appUrl}/learning/courses/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(handleFetchResponse)
            .then(data => {
                if (data.success) {
                    courses = courses.filter(c => c.id !== id);
                    closeConfirmModal();
                    renderCourses();
                    showToast('Course deleted successfully!');
                } else {
                     alert('Error deleting course: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }
    });
}
