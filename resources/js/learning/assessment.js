
        const exams = window.exams || [];
        const courses = window.courses || [];
        const competencies = window.competencies || [];
        
        // Multi-select state
        let selectedSkills = [];

        let currentTab = 'published';
        let currentStep = 1;

        window.onload = () => {
            renderTables();
            initSkillSelector();
        };

        // Initialize Skill Selector
        function initSkillSelector() {
            const searchInput = document.getElementById('skillSearchInput');
            const dropdown = document.getElementById('skillsListDropdown');
            const clearBtn = document.getElementById('clearSkillsBtn');
            const container = document.getElementById('skillsDropdownContainer');

            if (!searchInput || !dropdown) return;

            // Focus: Show dropdown
            searchInput.addEventListener('focus', () => {
                dropdown.classList.remove('hidden');
                renderSkillDropdown(searchInput.value);
            });

            // Input: Filter dropdown
            searchInput.addEventListener('input', (e) => {
                const val = e.target.value;
                clearBtn.classList.toggle('hidden', val.length === 0);
                renderSkillDropdown(val);
                dropdown.classList.remove('hidden');
            });

            // Click outside to close
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Clear button
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                clearBtn.classList.add('hidden');
                renderSkillDropdown('');
                searchInput.focus();
            });
        }
        
        // --- EVENT DELEGATION FOR DYNAMIC FORMS ---
        document.addEventListener('change', function(e) {
            // 1. Checkbox Toggle for Correct Answer
            if (e.target.classList.contains('correct-checkbox')) {
                const checkbox = e.target;
                const label = document.querySelector(`label[for="${checkbox.id}"]`);
                if (!label) return;

                if (checkbox.checked) {
                    label.classList.remove('border-slate-200', 'text-slate-300');
                    label.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white');
                } else {
                    label.classList.add('border-slate-200', 'text-slate-300');
                    label.classList.remove('bg-emerald-500', 'border-emerald-500', 'text-white');
                }
            }

            // 2. Image Preview Logic
            if (e.target.type === 'file' && e.target.closest('.group')) {
                const input = e.target;
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    const container = input.closest('.group');
                    
                    reader.onload = function(evt) {
                        let preview = container.querySelector('.image-preview');
                        if (!preview) {
                            preview = document.createElement('div');
                            preview.className = 'image-preview mt-2 relative w-full h-32 bg-slate-100 rounded-lg overflow-hidden border border-slate-200';
                            container.appendChild(preview);
                        }
                        
                        preview.innerHTML = `
                            <img src="${evt.target.result}" class="w-full h-full object-cover">
                            <button type="button" class="absolute top-2 right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center text-rose-500 shadow-sm hover:bg-rose-50" onclick="removeImage(this)">
                                <i class='bx bx-x'></i>
                            </button>
                        `;
                        
                        const labelSpan = container.querySelector('.file-label span');
                        if(labelSpan) labelSpan.textContent = input.files[0].name;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
        });

        // Global Remove Image Helper
        window.removeImage = function(btn) {
            const preview = btn.closest('.image-preview');
            const container = preview.parentElement;
            const input = container.querySelector('input[type="file"]');
            const labelSpan = container.querySelector('.file-label span');
            
            if(input) input.value = ''; // Reset file input
            if(labelSpan) labelSpan.textContent = 'Choose image...';
            preview.remove();
        };

        // Render Dropdown List
        function renderSkillDropdown(filterText = '') {
            const dropdown = document.getElementById('skillsListDropdown');
            if (!dropdown) return;

            const normalizedFilter = filterText.toLowerCase().trim();
            
            // If we have competencies from backend, use them. Otherwise default to some examples if empty.
            // Note: competencies is usually an array of objects {id, name, ...} or strings.
            // Let's assume objects based on typical Laravel usage, but check.
            // Controller says: $competencies = Competency::all(); -> collection of objects.
            
            let filtered = competencies.filter(c => {
                const name = c.name || c.title || c; // Handle object or string
                return name.toLowerCase().includes(normalizedFilter);
            });

            if (filtered.length === 0) {
                dropdown.innerHTML = `<div class="p-4 text-center text-slate-500 text-sm">No competencies found matching "${filterText}"</div>`;
                return;
            }

            dropdown.innerHTML = filtered.map(c => {
                const name = c.name || c.title || c;
                const id = c.id || name;
                const isSelected = selectedSkills.some(s => s.id == id);
                
                return `
                    <div class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors" 
                         onclick="toggleSkillSelection('${id}', '${name.replace(/'/g, "\\'")}')">
                        <div class="w-5 h-5 rounded border ${isSelected ? 'bg-blue-600 border-blue-600' : 'border-slate-300 bg-white'} flex items-center justify-center transition-all">
                            ${isSelected ? '<i class="bx bx-check text-white text-sm"></i>' : ''}
                        </div>
                        <span class="text-sm text-slate-700 font-medium ${isSelected ? 'text-blue-600' : ''}">${name}</span>
                    </div>
                `;
            }).join('');
        }

        // Toggle Selection
        window.toggleSkillSelection = function(id, name) {
            const index = selectedSkills.findIndex(s => s.id == id);
            if (index >= 0) {
                selectedSkills.splice(index, 1);
            } else {
                selectedSkills.push({ id, name });
            }
            
            // Update UI
            renderSkillDropdown(document.getElementById('skillSearchInput').value);
            renderSelectedTags();
            updateHiddenInput();
        };

        // Render Selected Tags
        function renderSelectedTags() {
            const container = document.getElementById('selectedSkillsContainer');
            if (!container) return;

            container.innerHTML = selectedSkills.map(s => `
                <div class="inline-flex items-center gap-1 bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-sm font-bold animate-in zoom-in-95 duration-200">
                    <span>${s.name}</span>
                    <button onclick="toggleSkillSelection('${s.id}', '${s.name.replace(/'/g, "\\'")}')" class="hover:text-blue-900 transition-colors ml-1">
                        <i class='bx bx-x text-base'></i>
                    </button>
                </div>
            `).join('');
        }

        // Update Hidden Input for Form Submission
        function updateHiddenInput() {
            const input = document.getElementById('examSkills');
            if (input) {
                // Send comma separated names as the backend expects string
                input.value = selectedSkills.map(s => s.name).join(',');
            }
        }

        // Expose functions to window
        window.openModal = openModal;
        window.closeModal = closeModal;
        window.applyFilters = applyFilters;
        window.triggerSuccess = triggerSuccess;
        window.resetFilters = resetFilters;
        window.switchTab = switchTab;
        window.showDetails = showDetails;
        window.navigateStep = navigateStep;
        window.addAnswerOption = addAnswerOption;
        window.addQuestion = addQuestion;
        window.submitExam = submitExam;

        function renderTables() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const scopeTerm = document.getElementById('scopeFilter').value;
            const proficiencyTerm = document.getElementById('proficiencyFilter').value;

            const filtered = exams.filter(e => {
                const matchesSearch = e.title.toLowerCase().includes(searchTerm) || e.course.toLowerCase().includes(searchTerm);
                const matchesScope = scopeTerm === 'all' || e.scope === scopeTerm;
                const matchesProficiency = proficiencyTerm === 'all' || e.proficiency === proficiencyTerm;
                return matchesSearch && matchesScope && matchesProficiency;
            });

            const pData = filtered.filter(e => e.status === 'published');
            const dData = filtered.filter(e => e.status === 'draft'); // Matches backend 'draft'

            document.getElementById('publishedTableBody').innerHTML = pData.map(e => generateRow(e)).join('');
            document.getElementById('draftsTableBody').innerHTML = dData.map(e => generateRow(e)).join('');

            const activeLen = currentTab === 'published' ? pData.length : dData.length;
            document.getElementById(`tab-${currentTab}`).classList.toggle('hidden', activeLen === 0);
            document.getElementById('emptyState').classList.toggle('hidden', activeLen > 0);
        }

        function generateRow(e) {
            const scopeCols = { internal: 'bg-blue-50 text-blue-700', departmental: 'bg-purple-50 text-purple-700', personal: 'bg-amber-50 text-amber-700' };
            const modalId = e.status === 'published' ? 'viewModalPublished' : 'viewModalDraft';

            return `
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 font-medium">${e.course}</td>
                    <td class="px-6 py-4">${e.title}</td>
                    <td class="px-6 py-4"><span class="${scopeCols[e.scope] || 'bg-gray-50 text-gray-700'} px-2 py-1 rounded text-[10px] font-bold capitalize">${e.scope}</span></td>
                    <td class="px-6 py-4 text-center capitalize text-xs">${e.proficiency}</td>
                    <td class="px-6 py-4"><span class="flex items-center gap-1 ${e.status === 'published' ? 'text-emerald-500' : 'text-amber-500'} font-bold">● ${e.status}</span></td>
                    <td class="px-6 py-4 text-center">${e.items}</td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="showDetails(${e.id}, '${modalId}')" class="text-blue-600 font-bold hover:underline">View Details</button>
                    </td>
                </tr>
            `;
        }

        function showDetails(id, modalId) {
            const e = exams.find(item => item.id === id);
            if (!e) return;

            const prefix = modalId === 'viewModalPublished' ? 'view' : 'draft-view';
            const courseEl = document.getElementById(`${prefix}-course`);
            if(courseEl) courseEl.textContent = e.course;
            
            const titleEl = document.getElementById(`${prefix}-title`);
            if(titleEl) titleEl.textContent = e.title;
            
            const descEl = document.getElementById(`${prefix}-description`);
            if(descEl) descEl.textContent = e.description;

            // Render Questions
            const qList = document.getElementById(`${prefix}-questions-list`);
            const appUrlMeta = document.querySelector('meta[name="app-url"]');
            const appUrl = appUrlMeta ? appUrlMeta.getAttribute('content') : '';

            if (qList) {
                if (e.questions && e.questions.length > 0) {
                    qList.innerHTML = e.questions.map((q, i) => `
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-slate-700 text-sm">Question ${i+1}</span>
                                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">${q.points} pts</span>
                            </div>
                            <p class="text-slate-600 text-sm mb-3">${q.text}</p>
                            ${q.image ? `<div class="mb-3 rounded-lg overflow-hidden border border-slate-200"><img src="${appUrl}/${q.image}" class="w-full h-32 object-cover"></div>` : ''}
                            <div class="space-y-2">
                                ${q.options.map(opt => `
                                    <div class="flex items-center gap-2 text-sm ${opt.is_correct ? 'text-emerald-700 font-medium' : 'text-slate-500'}">
                                        <i class='bx ${opt.is_correct ? 'bxs-check-circle text-emerald-500' : 'bx-radio-circle'} text-lg'></i>
                                        <span>${opt.text}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('');
                } else {
                    qList.innerHTML = '<p class="text-slate-400 text-sm italic">No questions available.</p>';
                }
            }

            if (modalId === 'viewModalPublished') {
                const itemsEl = document.getElementById('view-items');
                if(itemsEl) itemsEl.textContent = e.items;
                
                const scopeEl = document.getElementById('view-scope');
                if(scopeEl) scopeEl.textContent = e.scope;
                
                const profEl = document.getElementById('view-proficiency');
                if(profEl) profEl.textContent = e.proficiency;
                
                const skillsEl = document.getElementById('view-skills');
                if(skillsEl && e.skills) skillsEl.innerHTML = e.skills.split(',').map(s => `<span class="bg-slate-100 px-3 py-1 rounded-full text-xs text-slate-600">${s.trim()}</span>`).join('');
            } else {
                const draftSkillsEl = document.getElementById('draft-view-skills');
                if(draftSkillsEl && e.skills) draftSkillsEl.innerHTML = e.skills.split(',').map(s => `<span class="bg-slate-100 px-3 py-1 rounded-full text-xs text-slate-600">${s.trim()}</span>`).join('');
            }

            openModal(modalId);
        }

        function navigateStep(dir) {
            const next = currentStep + dir;
            if (next < 1 || next > 3) return;

            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            document.getElementById(`step-${next}`).classList.remove('hidden');
            
            document.getElementById(`pill-${currentStep}`).classList.remove('active');
            if (dir > 0) document.getElementById(`pill-${currentStep}`).classList.add('completed');
            
            document.getElementById(`pill-${next}`).classList.add('active');
            document.getElementById(`pill-${next}`).classList.remove('completed');

            currentStep = next;
            document.getElementById('prevBtn').style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            document.getElementById('nextBtn').classList.toggle('hidden', currentStep === 3);
            document.getElementById('finishBtn').classList.toggle('hidden', currentStep !== 3);

            if (currentStep === 3) {
                populateReviewStep();
            }
        }

        function populateReviewStep() {
            // Step 1 Data
            const courseSelect = document.getElementById('courseSelect');
            const courseName = courseSelect.options[courseSelect.selectedIndex]?.text || 'N/A';
            const title = document.getElementById('examTitle').value || 'Untitled Exam';
            const scope = document.getElementById('examScope').value;
            
            document.getElementById('review-course').textContent = courseName;
            document.getElementById('review-title').textContent = title;
            document.getElementById('review-scope').textContent = scope;
            
            const skillsContainer = document.getElementById('review-skills');
            skillsContainer.innerHTML = selectedSkills.length > 0 
                ? selectedSkills.map(s => `<span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold">${s.name}</span>`).join('')
                : '<span class="text-slate-400 italic">No skills selected</span>';

            // Step 2 Data
            const questions = [];
            let totalPoints = 0;
            document.querySelectorAll('.question-block').forEach((block, index) => {
                const text = block.querySelector('.question-text').value;
                const points = parseInt(block.querySelector('.question-points').value) || 0;
                totalPoints += points;
                
                if (text.trim() !== '') {
                    questions.push({ index: index + 1, text, points });
                }
            });

            document.getElementById('review-total-questions').textContent = questions.length;
            document.getElementById('review-total-points').textContent = totalPoints;

            const questionsList = document.getElementById('review-questions-list');
            if (questions.length > 0) {
                questionsList.innerHTML = questions.map(q => `
                    <div class="bg-white p-2 rounded border border-slate-100 flex gap-2">
                        <span class="font-bold text-slate-400 text-xs w-5">#${q.index}</span>
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-xs text-slate-600">${q.text}</p>
                        </div>
                        <span class="text-xs font-bold text-slate-500">${q.points} pts</span>
                    </div>
                `).join('');
            } else {
                questionsList.innerHTML = '<div class="text-center text-slate-400 italic text-xs py-2">No questions added yet.</div>';
            }
        }

        // Toggle Option Correct (Deprecated but kept for manual calls if needed, now handled by delegation)
        window.toggleOptionCorrect = function(checkboxId) {
            // No-op or manual trigger if needed
        };

        // Image Preview Handler (Deprecated but kept for manual calls, now handled by delegation)
        window.handleImagePreview = function(input) {
             // No-op or manual trigger
        };

        function addAnswerOption(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const uniqueId = `opt-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
            
            const div = document.createElement('div');
            div.className = "flex items-center gap-3 group/row option-row animate-in fade-in slide-in-from-left-2 duration-200";
            div.innerHTML = `
                <div class="relative">
                    <input type="checkbox" id="${uniqueId}" class="hidden correct-checkbox">
                    <label for="${uniqueId}" title="Mark as correct answer" class="w-10 h-10 flex items-center justify-center border-2 border-slate-200 text-slate-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:text-emerald-500 transition-all">
                        <i class='bx bx-check text-2xl'></i>
                    </label>
                </div>
                <div class="flex-1 relative">
                    <input type="text" placeholder="Enter answer text..." class="option-text w-full p-3 pr-10 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white shadow-sm">
                </div>
                <button onclick="this.parentElement.remove()" class="w-10 h-10 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition">
                    <i class='bx bx-trash'></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        // Image Preview Handler (Deprecated but kept for manual calls, now handled by delegation)
        window.handleImagePreview = function(input) {
             // No-op or manual trigger
        };

        window.removeImage = function(btn) {
            const preview = btn.closest('.image-preview');
            const container = preview.parentElement;
            const input = container.querySelector('input[type="file"]');
            const label = container.querySelector('.file-label span');
            
            if(input) input.value = '';
            if(label) label.textContent = 'Choose image...';
            preview.remove();
        };

        function addQuestion() {
            const container = document.getElementById('questionsContainer');
            const questions = container.querySelectorAll('.question-block');
            const nextId = questions.length + 1;
            
            // Clone the first question block
            const template = questions[0].cloneNode(true);
            
            // Update question number
            template.setAttribute('data-id', nextId);
            template.querySelector('.question-number').textContent = nextId;
            
            // Reset inputs
            template.querySelector('.question-text').value = '';
            template.querySelector('.question-points').value = '1';
            
            // Remove any existing preview
            const existingPreview = template.querySelector('.image-preview');
            if(existingPreview) existingPreview.remove();

            // Update IDs and Labels to ensure uniqueness
            const fileInput = template.querySelector('input[type="file"]');
            const fileLabel = template.querySelector('.file-label');
            const optionsContainer = template.querySelector('.options-container');
            const addOptionBtn = template.querySelector('.add-option-btn');
            
            const newFileId = `q${nextId}-file`;
            const newOptionsId = `q${nextId}-options`;
            
            if(fileInput) {
                fileInput.id = newFileId;
                fileInput.value = ''; // Clear file
                // fileInput.setAttribute('onchange', 'handleImagePreview(this)'); // Handled by delegation
            }
            if(fileLabel) {
                fileLabel.setAttribute('for', newFileId);
                const span = fileLabel.querySelector('span');
                if(span) span.textContent = 'Choose image...';
            }
            
            if(optionsContainer) {
                optionsContainer.id = newOptionsId;
                optionsContainer.innerHTML = ''; // Clear options
                // Add default options (e.g., 2 empty options)
                addAnswerOption(newOptionsId);
                addAnswerOption(newOptionsId);
            }
            
            if(addOptionBtn) addOptionBtn.setAttribute('onclick', `addAnswerOption('${newOptionsId}')`);
            
            // Append before the button
            container.insertBefore(template, container.lastElementChild);
        }

        async function submitExam() {
            const courseId = document.getElementById('courseSelect').value;
            const title = document.getElementById('examTitle').value;
            const scope = document.getElementById('examScope').value;
            const skills = document.getElementById('examSkills').value;
            
            if (!title) {
                alert('Please enter an exam title');
                return;
            }

            const formData = new FormData();
            formData.append('course_id', courseId);
            formData.append('title', title);
            formData.append('type', 'exam');
            formData.append('status', 'published');
            formData.append('skills', skills);

            let questionCount = 0;
            let hasQuestions = false;

            document.querySelectorAll('.question-block').forEach((block, index) => {
                const text = block.querySelector('.question-text').value;
                const points = block.querySelector('.question-points').value;
                const fileInput = block.querySelector('input[type="file"]');
                
                if (text.trim() !== '') {
                    hasQuestions = true;
                    formData.append(`questions[${index}][text]`, text);
                    formData.append(`questions[${index}][type]`, 'multiple_choice');
                    formData.append(`questions[${index}][points]`, points);
                    
                    if (fileInput && fileInput.files[0]) {
                        formData.append(`questions[${index}][image]`, fileInput.files[0]);
                    }

                    let optionIndex = 0;
                    block.querySelectorAll('.option-row').forEach(row => {
                        const optText = row.querySelector('.option-text').value;
                        const isCorrect = row.querySelector('.correct-checkbox').checked;
                        if(optText.trim() !== '') {
                            formData.append(`questions[${index}][options][${optionIndex}][text]`, optText);
                            formData.append(`questions[${index}][options][${optionIndex}][is_correct]`, isCorrect ? '1' : '0');
                            optionIndex++;
                        }
                    });
                    questionCount++;
                }
            });
            
            if (!hasQuestions) {
                if(!confirm("You are creating an exam with no questions. Continue?")) return;
            }

            // Get CSRF token
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            
            // Get App URL
            const appUrlMeta = document.querySelector('meta[name="app-url"]');
            const appUrl = appUrlMeta ? appUrlMeta.getAttribute('content') : '';

            try {
                const response = await fetch(`${appUrl}/learning/assessments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        // 'Content-Type': 'multipart/form-data' // DO NOT SET CONTENT-TYPE for FormData, browser sets it with boundary
                    },
                    body: formData
                });

                const result = await response.json();
                
                if (result.success || response.ok) {
                    triggerSuccess();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    let errorMessage = result.message || 'Unknown error';
                    if (result.errors) {
                        errorMessage += '\n' + Object.values(result.errors).flat().join('\n');
                    }
                    alert('Error creating exam: ' + errorMessage);
                    console.error(result);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while saving the exam.');
            }
        }

        function openModal(id) { 
            const el = document.getElementById(id);
            if(el) {
                el.classList.remove('hidden'); 
                document.body.classList.add('modal-active'); 
            }
        }
        function closeModal(id) { 
            const el = document.getElementById(id);
            if(el) {
                el.classList.add('hidden'); 
                document.body.classList.remove('modal-active'); 
            }
        }
        function applyFilters() { renderTables(); }
        function triggerSuccess() { 
            closeModal('confirmModal'); // Assuming this exists or is called from confirm
            closeModal('createModal'); 
            closeModal('viewModalDraft'); 
            openModal('successModal'); 
        }
        function resetFilters() { 
            document.getElementById('searchInput').value = ''; 
            document.getElementById('scopeFilter').value = 'all'; 
            document.getElementById('proficiencyFilter').value = 'all'; 
            renderTables(); 
        }

        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('tabBtn-published').classList.toggle('text-blue-600', tab === 'published');
            document.getElementById('tabBtn-published').classList.toggle('border-b-2', tab === 'published');
            document.getElementById('tabBtn-drafts').classList.toggle('text-blue-600', tab === 'drafts');
            document.getElementById('tabBtn-drafts').classList.toggle('border-b-2', tab === 'drafts');
            document.getElementById('tab-published').classList.toggle('hidden', tab !== 'published');
            document.getElementById('tab-drafts').classList.toggle('hidden', tab !== 'drafts');
            renderTables();
        }
