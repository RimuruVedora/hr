let competencies = [];
let mappings = [];
let currentMappingId = null;
let currentMappingSelectedCompIds = [];
let assignSelectedCompIds = [];
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

async function fetchCompetencies() {
    // Fetch Competencies
    const listUrl = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.list) ? window.COMPETENCY_ENDPOINTS.list : '/competency/list';
    const res = await fetch(listUrl, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    competencies = data.items || [];
    
    // Fetch Job Roles (Mappings)
    const mappingUrl = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.jobRolesList) ? window.COMPETENCY_ENDPOINTS.jobRolesList : '/job-roles';
    try {
        const resMap = await fetch(mappingUrl, { headers: { 'Accept': 'application/json' } });
        const mapData = await resMap.json();
        mappings = Array.isArray(mapData) ? mapData : [];
    } catch (e) {
        console.error('Failed to fetch job roles', e);
        mappings = [];
    }

    // Update Stats
    if (data.stats) {

        const sTotal = document.getElementById('stat-total');
        const sOrg = document.getElementById('stat-org');
        const sGaps = document.getElementById('stat-gaps');
        const sAvg = document.getElementById('stat-avg');

        if (sTotal) sTotal.innerText = data.stats.total;
        if (sOrg) sOrg.innerText = data.stats.orgWide;
        if (sGaps) sGaps.innerText = data.stats.criticalGaps;
        if (sAvg) sAvg.innerText = data.stats.avgProficiency + '%';
    }

    renderCompetencies();
    renderMappingTable();
    updateMappingStats();
}

        function renderCompetencies(data = null) {
            const displayData = data || competencies;
            const tableBody = document.getElementById('competency-table-body');
            
            if (displayData.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"></i>
                                <p class="text-sm font-medium">No competencies found matching your criteria.</p>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                tableBody.innerHTML = displayData.map(comp => `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-800">${comp.name}</p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-tight">ID: C00${comp.id}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md border border-slate-200">${comp.category}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-slate-600 font-medium flex items-center gap-1.5">
                            <i class="fa-solid fa-layer-group text-slate-300"></i>
                            ${comp.scope}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-blue-600 font-bold text-xs">
                            <i class="fa-solid fa-signal text-[10px]"></i>
                            ${comp.proficiency}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full text-[10px] font-bold uppercase border ${
                            comp.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'
                        }">
                            <span class="w-1.5 h-1.5 rounded-full ${comp.status === 'Active' ? 'bg-emerald-600' : 'bg-amber-600'}"></span>
                            ${comp.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-1">
                            <button onclick="viewCompetency(${comp.id})" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"><i class="fa-solid fa-eye text-sm"></i></button>
                            <button onclick="editCompetency(${comp.id})" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"><i class="fa-solid fa-pen-to-square text-sm"></i></button>
                            <button onclick="confirmAction('delete', ${comp.id})" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"><i class="fa-solid fa-trash text-sm"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
            }
            
            const total = competencies.length;
            const org = competencies.filter(c => c.scope === 'Organization-wide').length;
            // Stats are now updated from backend response in fetchCompetencies
            // const sTotal = document.getElementById('stat-total'); if (sTotal) sTotal.innerText = total;
            // const sOrg = document.getElementById('stat-org'); if (sOrg) sOrg.innerText = org;
        }

        function applyFilters() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('filterCategory').value;

            const filtered = competencies.filter(comp => {
                const matchesSearch = comp.name.toLowerCase().includes(searchText) || 
                                      (comp.desc && (comp.desc || '').toLowerCase().includes(searchText));
                const matchesCategory = categoryFilter === '' || comp.category === categoryFilter;
                return matchesSearch && matchesCategory;
            });

            renderCompetencies(filtered);
        }

        function toggleModal(id, show) {
            const overlay = document.getElementById(id + 'ModalOverlay');
            const content = document.getElementById(id + 'ModalContent');
            if (!overlay || !content) return;

            if (show) {
                overlay.classList.remove('hidden');
                setTimeout(() => { overlay.style.opacity = '1'; content.style.transform = 'scale(1)'; }, 10);
            } else {
                overlay.style.opacity = '0'; content.style.transform = 'scale(0.95)';
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        function openFormModal(isEdit = false) {
            document.getElementById('modalTitle').innerText = isEdit ? 'Update Competency Profile' : 'Define New Competency';
            if (!isEdit) {
                document.getElementById('editingId').value = '';
                document.getElementById('newCompTitle').value = '';
                document.getElementById('newCompDesc').value = '';
                document.getElementById('newCompCategory').selectedIndex = 0;
                document.getElementById('newCompScope').selectedIndex = 0;
                document.getElementById('newCompProficiency').selectedIndex = 0;
                document.getElementById('newCompWeight').selectedIndex = 1;
                document.getElementById('newCompStatus').selectedIndex = 0;
            }
            toggleModal('form', true);
        }

        function closeFormModal() { toggleModal('form', false); }

        function editCompetency(id) {
            const comp = competencies.find(c => c.id === id);
            if (!comp) return;
            document.getElementById('editingId').value = comp.id;
            document.getElementById('newCompTitle').value = comp.name;
            document.getElementById('newCompCategory').value = comp.category;
            document.getElementById('newCompScope').value = comp.scope;
            document.getElementById('newCompProficiency').value = comp.proficiency;
            document.getElementById('newCompWeight').value = comp.weight;
            document.getElementById('newCompStatus').value = comp.status;
            document.getElementById('newCompDesc').value = comp.desc || '';
            openFormModal(true);
        }

        function viewCompetency(id) {
            const comp = competencies.find(c => c.id === id);
            if (!comp) return;
            document.getElementById('viewBody').innerHTML = `
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <button onclick="closeViewModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <h4 class="text-2xl font-black text-slate-900">${comp.name}</h4>
                <div class="flex gap-2 mt-2 mb-6">
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-bold uppercase border border-blue-100">${comp.category}</span>
                    <span class="px-2 py-0.5 bg-slate-50 text-slate-600 rounded text-[10px] font-bold uppercase border border-slate-100">${comp.status}</span>
                </div>
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Description</label>
                        <p class="text-slate-600 text-sm leading-relaxed">${comp.desc || 'Detailed competency definition not provided.'}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Org Scope</label>
                            <p class="text-slate-900 font-bold text-sm">${comp.scope}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Target Proficiency</label>
                            <p class="text-blue-600 font-bold text-sm">${comp.proficiency}</p>
                        </div>
                    </div>
                </div>
            `;
            toggleModal('view', true);
        }

        function closeViewModal() { toggleModal('view', false); }

        function confirmAction(type, id = null) {
            const title = document.getElementById('confirmTitle');
            const text = document.getElementById('confirmText');
            const btn = document.getElementById('confirmBtn');
            const icon = document.getElementById('confirmIcon');

            if (type === 'delete') {
                title.innerText = 'Confirm Removal';
                text.innerText = 'This will permanently remove this item from the competency framework.';
                btn.innerText = 'Delete Entry';
                btn.className = 'flex-1 px-4 py-2.5 text-sm font-bold text-white bg-rose-600 rounded-xl shadow-lg shadow-rose-200 hover:bg-rose-700 transition-all';
                icon.className = 'w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl bg-rose-100 text-rose-600';
                icon.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                btn.onclick = () => performDelete(id);
            } else if (type === 'mapping-save') {
                title.innerText = 'Save Mapping?';
                text.innerText = 'Confirm alignment changes for this job role mapping.';
                btn.innerText = 'Commit Mapping';
                btn.className = 'flex-1 px-4 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all';
                icon.className = 'w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl bg-blue-100 text-blue-600';
                icon.innerHTML = '<i class="fa-solid fa-diagram-project"></i>';
                btn.onclick = () => saveMapping();
            } else if (type === 'assign-save') {
                title.innerText = 'Save Assignment?';
                text.innerText = 'Confirm competency assignment for this job role.';
                btn.innerText = 'Save Assignment';
                btn.className = 'flex-1 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all';
                icon.className = 'w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl bg-indigo-100 text-indigo-600';
                icon.innerHTML = '<i class="fa-solid fa-user-tag"></i>';
                btn.onclick = () => saveAssignment();
            } else {
                title.innerText = 'Save Framework?';
                text.innerText = 'Confirm operational changes to the system registry.';
                btn.innerText = 'Commit Changes';
                btn.className = 'flex-1 px-4 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all';
                icon.className = 'w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl bg-blue-100 text-blue-600';
                icon.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i>';
                btn.onclick = () => saveCompetency();
            }
            toggleModal('confirm', true);
        }

        function closeConfirm() { toggleModal('confirm', false); }

        async function saveCompetency() {
            const titleInput = document.getElementById('newCompTitle');
            if (!titleInput.value) return;

            const editId = document.getElementById('editingId').value;
            const payload = {
                name: titleInput.value,
                category: document.getElementById('newCompCategory').value,
                scope: document.getElementById('newCompScope').value,
                proficiency: document.getElementById('newCompProficiency').value,
                weight: document.getElementById('newCompWeight').value,
                status: document.getElementById('newCompStatus').value,
                desc: document.getElementById('newCompDesc').value
            };

            if (editId) {
                const updateBase = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.updateBase) ? window.COMPETENCY_ENDPOINTS.updateBase : '/competency';
                const res = await fetch(`${updateBase}/${editId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                if (res.ok) showSuccess('Registry Updated', 'Framework synchronized.');
            } else {
                const storeUrl = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.store) ? window.COMPETENCY_ENDPOINTS.store : '/competency';
                const res = await fetch(storeUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                if (res.ok) showSuccess('Entry Created', 'New competency active.');
            }
            await fetchCompetencies();
            closeConfirm(); closeFormModal();
        }

        async function performDelete(id) {
            const deleteBase = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.deleteBase) ? window.COMPETENCY_ENDPOINTS.deleteBase : '/competency';
            await fetch(`${deleteBase}/${id}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            await fetchCompetencies();
            closeConfirm(); showSuccess('Entry Removed', 'Item deleted from framework.');
        }

        function showSuccess(title, text) {
            document.getElementById('successTitle').innerText = title;
            document.getElementById('successText').innerText = text;
            toggleModal('success', true);
            setTimeout(() => toggleModal('success', false), 1500);
        }

        function renderMappingTable(data = null) {
            const displayData = data || mappings;
            const tableBody = document.getElementById('mapping-table-body');
            if (!tableBody) return;

            if (!displayData.length) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"></i>
                                <p class="text-sm font-medium">No competency mappings defined yet.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = displayData.map(entry => {
                const assignedCount = (entry.competencies || []).length;
                const compNames = (entry.competencies || []).map(c => c.name);
                const preview = compNames.slice(0, 3).join(', ');
                const moreCount = compNames.length > 3 ? ` +${compNames.length - 3} more` : '';
                let weightingLabel = 'Medium';
                let weightingColorClass = 'bg-blue-50 text-blue-700 border-blue-200';
                const w = parseInt(entry.weighting);
                
                if (w <= 1) {
                    weightingLabel = 'Very Low';
                    weightingColorClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                } else if (w === 2) {
                    weightingLabel = 'Low';
                    weightingColorClass = 'bg-teal-50 text-teal-700 border-teal-200';
                } else if (w === 3) {
                    weightingLabel = 'Medium';
                    weightingColorClass = 'bg-blue-50 text-blue-700 border-blue-200';
                } else if (w === 4) {
                    weightingLabel = 'High';
                    weightingColorClass = 'bg-amber-50 text-amber-700 border-amber-200';
                } else if (w >= 5) {
                    weightingLabel = 'Critical';
                    weightingColorClass = 'bg-rose-50 text-rose-700 border-rose-200';
                }

                return `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-800">${entry.name}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-700">${assignedCount} competencies</p>
                            ${compNames.length ? `<p class="text-[11px] text-slate-400 mt-1">${preview}${moreCount}</p>` : ''}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full text-[10px] font-bold uppercase border ${weightingColorClass}">
                                <i class="fa-solid fa-signal text-[10px]"></i>
                                ${weightingLabel}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-600 line-clamp-2">${entry.description || ''}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1">
                                <button onclick="viewMapping(${entry.id})" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </button>
                                <button onclick="openMappingEdit(${entry.id})" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function updateMappingStats() {
            const utilizationEl = document.getElementById('stat-framework-utilization');
            const densityEl = document.getElementById('stat-skill-density');
            const alignmentEl = document.getElementById('stat-role-alignment');
            const rolesEl = document.getElementById('stat-job-roles');

            if (!utilizationEl && !densityEl && !alignmentEl && !rolesEl) return;

            const totalCompetencies = competencies.length;
            const totalRoles = mappings.length;
            const distinctAssigned = new Set();
            let totalAssignedPerRole = 0;
            let rolesWithAssignments = 0;

            mappings.forEach(entry => {
                const comps = entry.competencies || [];
                if (comps.length) rolesWithAssignments += 1;
                comps.forEach(c => distinctAssigned.add(c.id));
                totalAssignedPerRole += comps.length;
            });

            const utilization = totalCompetencies ? Math.round((distinctAssigned.size / totalCompetencies) * 100) : 0;
            const density = totalRoles ? (totalAssignedPerRole / totalRoles).toFixed(1) : '0.0';
            const alignment = totalRoles ? Math.round((rolesWithAssignments / totalRoles) * 100) : 0;

            if (utilizationEl) utilizationEl.innerText = utilization + '%';
            if (densityEl) densityEl.innerText = density;
            if (alignmentEl) alignmentEl.innerText = alignment + '%';
            if (rolesEl) rolesEl.innerText = totalRoles;
        }

        function openMappingEdit(id) {
            const entry = mappings.find(m => m.id === id);
            if (!entry) return;
            currentMappingId = id;
            // Fix: Map from entry.competencies objects to IDs, as entry.competencyIds doesn't exist from backend
            currentMappingSelectedCompIds = (entry.competencies && Array.isArray(entry.competencies)) 
                ? entry.competencies.map(c => c.id) 
                : [];

            const roleInput = document.getElementById('mappingJobRole');
            const descInput = document.getElementById('mappingDescription');
            const slider = document.getElementById('mappingWeightSlider');
            const idInput = document.getElementById('mappingEditingId');
            const searchInput = document.getElementById('mappingCompSearch');

            if (roleInput) roleInput.value = entry.name;
            if (descInput) descInput.value = entry.description || '';
            if (slider) {
                slider.value = entry.weighting || 3;
                updateMappingWeightLabel();
            }
            if (idInput) idInput.value = entry.id;
            if (searchInput) searchInput.value = '';

            renderMappingCompList();
            toggleModal('mappingEdit', true);
        }

        function closeMappingEditModal() {
            toggleModal('mappingEdit', false);
            currentMappingId = null;
            currentMappingSelectedCompIds = [];
        }

        function renderMappingCompList(filterText = '') {
            const list = document.getElementById('mappingCompList');
            if (!list) return;
            const text = (filterText || '').toLowerCase();
            const source = competencies || [];
            const filtered = text ? source.filter(c => c.name.toLowerCase().includes(text)) : source;

            if (!filtered.length) {
                list.innerHTML = '<p class="text-xs text-slate-500 py-2 px-1">No competencies match the current search.</p>';
                return;
            }

            list.innerHTML = filtered.map(comp => {
                const checked = currentMappingSelectedCompIds.includes(comp.id) ? 'checked' : '';
                return `
                    <label class="flex items-center gap-2 py-1 px-1 rounded hover:bg-slate-100 cursor-pointer">
                        <input type="checkbox" class="mapping-comp-checkbox rounded border-slate-300 text-blue-600" data-comp-id="${comp.id}" ${checked}>
                        <span class="text-sm text-slate-700">${comp.name}</span>
                    </label>
                `;
            }).join('');
        }

        function handleMappingCompCheckboxChange(event) {
            if (!event.target.classList.contains('mapping-comp-checkbox')) return;
            if (currentMappingId === null) return;
            const id = parseInt(event.target.getAttribute('data-comp-id') || '0', 10);
            if (!id) return;
            if (event.target.checked) {
                if (!currentMappingSelectedCompIds.includes(id)) currentMappingSelectedCompIds.push(id);
            } else {
                currentMappingSelectedCompIds = currentMappingSelectedCompIds.filter(v => v !== id);
            }
        }

        function updateMappingWeightLabel() {
            const slider = document.getElementById('mappingWeightSlider');
            const label = document.getElementById('mappingWeightLabel');
            if (!slider || !label) return;
            const value = parseInt(slider.value || '0', 10);
            let text = 'Medium';
            if (value <= 1) text = 'Very Low';
            else if (value === 2) text = 'Low';
            else if (value === 4) text = 'High';
            else if (value >= 5) text = 'Critical';
            label.innerText = text;
        }

        async function saveMapping() {
            if (currentMappingId === null) return;
            const slider = document.getElementById('mappingWeightSlider');
            const entry = mappings.find(m => m.id === currentMappingId);
            if (!entry) return;
            
            let newWeight = 3;
            if (slider) {
                newWeight = parseInt(slider.value || '3', 10);
            }

            const payload = {
                competencies: currentMappingSelectedCompIds,
                weighting: newWeight
            };

            const updateBase = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.jobRolesUpdateBase) ? window.COMPETENCY_ENDPOINTS.jobRolesUpdateBase : '/job-roles';

            try {
                const res = await fetch(`${updateBase}/${currentMappingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    await fetchCompetencies(); // Reload data from server
                    closeConfirm();
                    closeMappingEditModal();
                    showSuccess('Mapping Updated', 'Role competency mapping saved.');
                } else {
                    alert('Failed to update mapping.');
                    closeConfirm();
                }
            } catch (e) {
                console.error(e);
                alert('Error updating mapping.');
                closeConfirm();
            }
        }

        function viewMapping(id) {
            const entry = mappings.find(m => m.id === id);
            const body = document.getElementById('mappingViewBody');
            if (!entry || !body) return;
            
            // Use entry.competencies (array of objects) directly
            const compNames = (entry.competencies || []).map(c => c.name).filter(Boolean);
            
            const listHtml = compNames.length
                ? `<ul class="mt-2 space-y-1 text-sm text-slate-700">${compNames.map(n => `<li class="flex items-center gap-2"><i class="fa-solid fa-circle text-[6px] text-slate-400"></i><span>${n}</span></li>`).join('')}</ul>`
                : '<p class="text-sm text-slate-500 mt-2">No competencies assigned yet.</p>';

            let weightingLabel = 'Medium';
            let weightingColorClass = 'text-blue-600';
            const w = parseInt(entry.weighting);
            if (w <= 1) { weightingLabel = 'Very Low'; weightingColorClass = 'text-emerald-600'; }
            else if (w === 2) { weightingLabel = 'Low'; weightingColorClass = 'text-teal-600'; }
            else if (w === 4) { weightingLabel = 'High'; weightingColorClass = 'text-amber-600'; }
            else if (w >= 5) { weightingLabel = 'Critical'; weightingColorClass = 'text-rose-600'; }

            body.innerHTML = `
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <button onclick="closeMappingViewModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <h4 class="text-2xl font-black text-slate-900">${entry.name}</h4>
                <p class="text-sm text-slate-500 mt-1">Weighting scale: <span class="font-bold ${weightingColorClass}">${weightingLabel} (${entry.weighting})</span></p>
                <div class="mt-4">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Description</label>
                    <p class="text-slate-600 text-sm leading-relaxed">${entry.description || 'No description provided for this job role.'}</p>
                </div>
                <div class="mt-6">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Competencies Assigned</label>
                    ${listHtml}
                </div>
            `;
            toggleModal('mappingView', true);
        }

        function closeMappingViewModal() {
            toggleModal('mappingView', false);
        }

        function openAssignModal() {
            // Populate Job Roles
            const select = document.getElementById('assignJobRole');
            if (select) {
                select.innerHTML = '<option value="">Select a Job Role...</option>';
                mappings.forEach(role => {
                    const opt = document.createElement('option');
                    opt.value = role.id;
                    opt.text = role.name;
                    select.appendChild(opt);
                });
            }

            // Reset selection
            assignSelectedCompIds = [];
            renderAssignCompList();
            updateAssignWeightingUI();

            // Show Modal
            const overlay = document.getElementById('assignModalOverlay');
            const content = document.getElementById('assignModalContent');
            if (overlay && content) {
                overlay.classList.remove('hidden');
                setTimeout(() => { overlay.style.opacity = '1'; content.style.transform = 'scale(1)'; }, 10);
            }
        }

        function closeAssignModal() {
            const overlay = document.getElementById('assignModalOverlay');
            const content = document.getElementById('assignModalContent');
            if (!overlay || !content) return;
            overlay.style.opacity = '0'; content.style.transform = 'scale(0.95)';
            setTimeout(() => overlay.classList.add('hidden'), 300);
            assignSelectedCompIds = [];
        }

        function renderAssignCompList(filterText = '') {
            const list = document.getElementById('assignCompList');
            if (!list) return;
            const text = (filterText || '').toLowerCase();
            const source = competencies || [];
            const filtered = text ? source.filter(c => c.name.toLowerCase().includes(text)) : source;

            if (!filtered.length) {
                list.innerHTML = '<p class="text-xs text-slate-500 py-2 px-1">No competencies match the current search.</p>';
                return;
            }

            list.innerHTML = filtered.map(comp => {
                const checked = assignSelectedCompIds.includes(comp.id) ? 'checked' : '';
                return `
                    <label class="flex items-center gap-2 py-1.5 px-2 rounded hover:bg-slate-100 cursor-pointer border-b border-slate-50 last:border-0">
                        <input type="checkbox" class="assign-comp-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-comp-id="${comp.id}" ${checked}>
                        <div>
                            <p class="text-sm font-medium text-slate-700">${comp.name}</p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-wider">${comp.weight || 'Medium'}</p>
                        </div>
                    </label>
                `;
            }).join('');
        }

        function handleAssignCheckboxChange(event) {
            if (!event.target.classList.contains('assign-comp-checkbox')) return;
            const id = parseInt(event.target.getAttribute('data-comp-id') || '0', 10);
            if (!id) return;
            if (event.target.checked) {
                if (!assignSelectedCompIds.includes(id)) assignSelectedCompIds.push(id);
            } else {
                assignSelectedCompIds = assignSelectedCompIds.filter(v => v !== id);
            }
            updateAssignWeightingUI();
        }

        function updateAssignWeightingUI() {
            const bar = document.getElementById('assignWeightBar');
            const label = document.getElementById('assignWeightLabel');
            if (!bar || !label) return;

            if (assignSelectedCompIds.length === 0) {
                bar.style.width = '0%';
                bar.className = 'h-full bg-slate-300 w-0 transition-all duration-300';
                label.innerText = 'None';
                return;
            }

            let totalScore = 0;
            assignSelectedCompIds.forEach(id => {
                const comp = competencies.find(c => c.id === id);
                if (comp) {
                    let w = 3;
                    const cw = (comp.weight || 'Medium').toLowerCase();
                    if (cw === 'low') w = 1;
                    else if (cw === 'medium') w = 3;
                    else if (cw === 'high') w = 5;
                    totalScore += w;
                }
            });

            const avg = totalScore / assignSelectedCompIds.length;
            const percent = (avg / 5) * 100;
            
            bar.style.width = `${percent}%`;
            
            let text = 'Medium';
            let color = 'bg-blue-500';
            
            if (avg <= 1.5) { text = 'Low'; color = 'bg-emerald-500'; }
            else if (avg <= 2.5) { text = 'Medium-Low'; color = 'bg-teal-500'; }
            else if (avg <= 3.5) { text = 'Medium'; color = 'bg-blue-500'; }
            else if (avg <= 4.5) { text = 'High'; color = 'bg-indigo-500'; }
            else { text = 'Critical'; color = 'bg-rose-500'; }

            bar.className = `h-full ${color} transition-all duration-300`;
            label.innerText = text;
        }

        async function saveAssignment() {
            const roleId = document.getElementById('assignJobRole').value;
            if (!roleId) {
                alert('Please select a Job Role.');
                return;
            }

            // Calculate final weighting integer (1-5)
            let weighting = 3;
            if (assignSelectedCompIds.length > 0) {
                let totalScore = 0;
                assignSelectedCompIds.forEach(id => {
                    const comp = competencies.find(c => c.id === id);
                    if (comp) {
                        let w = 3;
                        const cw = (comp.weight || 'Medium').toLowerCase();
                        if (cw === 'low') w = 1;
                        else if (cw === 'medium') w = 3;
                        else if (cw === 'high') w = 5;
                        totalScore += w;
                    }
                });
                weighting = Math.round(totalScore / assignSelectedCompIds.length);
                if (weighting < 1) weighting = 1;
                if (weighting > 5) weighting = 5;
            }

            const payload = {
                competencies: assignSelectedCompIds,
                weighting: weighting
            };

            const updateBase = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.jobRolesUpdateBase) ? window.COMPETENCY_ENDPOINTS.jobRolesUpdateBase : '/job-roles';
            
            try {
                const res = await fetch(`${updateBase}/${roleId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload)
                });
                
                if (res.ok) {
                    showSuccess('Assignment Saved', 'Job role competencies updated.');
                    closeAssignModal();
                    fetchCompetencies(); // Reload all data
                } else {
                    alert('Failed to save assignment.');
                }
            } catch (e) {
                console.error(e);
                alert('Error saving assignment.');
            }
            closeConfirm();
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchCompetencies();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) searchInput.addEventListener('input', applyFilters);
            const filterCategory = document.getElementById('filterCategory');
            if (filterCategory) filterCategory.addEventListener('change', applyFilters);
            
            // Mapping Search
            const mappingSearchInput = document.getElementById('mappingSearchInput');
            if (mappingSearchInput) mappingSearchInput.addEventListener('input', () => {
                const value = mappingSearchInput.value.toLowerCase();
                const filtered = mappings.filter(entry => entry.name.toLowerCase().includes(value));
                renderMappingTable(filtered);
            });

            // Assign Modal Listeners
            const assignCompSearch = document.getElementById('assignCompSearch');
            if (assignCompSearch) assignCompSearch.addEventListener('input', () => {
                renderAssignCompList(assignCompSearch.value);
            });
            const assignCompList = document.getElementById('assignCompList');
            if (assignCompList) assignCompList.addEventListener('change', handleAssignCheckboxChange);
            const assignJobRole = document.getElementById('assignJobRole');
            if (assignJobRole) assignJobRole.addEventListener('change', (e) => {
                // Optional: Pre-fill selections if user picks a role that already has assignments?
                // The prompt didn't strictly ask for it, but it's good UX.
                // "Assign" usually implies overwriting or adding.
                // If I select a role, I should probably show its current assignments.
                const roleId = parseInt(e.target.value || '0');
                if (roleId) {
                    const role = mappings.find(m => m.id === roleId);
                    if (role && role.competencies) {
                        assignSelectedCompIds = role.competencies.map(c => c.id);
                        renderAssignCompList();
                        updateAssignWeightingUI();
                    }
                }
            });

            const mappingCompSearch = document.getElementById('mappingCompSearch');
            if (mappingCompSearch) mappingCompSearch.addEventListener('input', () => {
                renderMappingCompList(mappingCompSearch.value.toLowerCase());
            });
            const mappingCompList = document.getElementById('mappingCompList');
            if (mappingCompList) mappingCompList.addEventListener('change', handleMappingCompCheckboxChange);
            const weightSlider = document.getElementById('mappingWeightSlider');
            if (weightSlider) weightSlider.addEventListener('input', updateMappingWeightLabel);

            const tabFramework = document.getElementById('tabFramework');
            const tabMapping = document.getElementById('tabMapping');
            const frameworkPanel = document.getElementById('frameworkPanel');
            const mappingPanel = document.getElementById('mappingPanel');
            const frameworkControls = document.getElementById('frameworkControls');
            const mappingControls = document.getElementById('mappingControls');

            if (tabFramework && tabMapping && frameworkPanel && mappingPanel) {
                tabFramework.addEventListener('click', () => {
                    tabFramework.classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
                    tabFramework.classList.remove('text-slate-500');
                    tabMapping.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
                    tabMapping.classList.add('text-slate-500');
                    frameworkPanel.classList.remove('hidden');
                    mappingPanel.classList.add('hidden');
                    if (frameworkControls) frameworkControls.classList.remove('hidden');
                    if (mappingControls) mappingControls.classList.add('hidden');
                });

                tabMapping.addEventListener('click', () => {
                    tabMapping.classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
                    tabMapping.classList.remove('text-slate-500');
                    tabFramework.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
                    tabFramework.classList.add('text-slate-500');
                    frameworkPanel.classList.add('hidden');
                    mappingPanel.classList.remove('hidden');
                    if (frameworkControls) frameworkControls.classList.add('hidden');
                    if (mappingControls) mappingControls.classList.remove('hidden');
                    renderMappingTable();
                });
            }
        });
        window.openFormModal = openFormModal;
        window.closeFormModal = closeFormModal;
        window.viewCompetency = viewCompetency;
        window.editCompetency = editCompetency;
        window.confirmAction = confirmAction;
        window.performDelete = performDelete;
        window.applyFilters = applyFilters;
        window.openMappingEdit = openMappingEdit;
        window.closeMappingEditModal = closeMappingEditModal;
        window.viewMapping = viewMapping;
        window.closeMappingViewModal = closeMappingViewModal;
        window.openAssignModal = openAssignModal;
        window.closeAssignModal = closeAssignModal;
        window.saveAssignment = saveAssignment;
        window.closeConfirm = closeConfirm;
        window.saveMapping = saveMapping;
