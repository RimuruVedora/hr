let competencies = [];
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
async function fetchCompetencies() {
    const listUrl = (window.COMPETENCY_ENDPOINTS && window.COMPETENCY_ENDPOINTS.list) ? window.COMPETENCY_ENDPOINTS.list : '/competency/list';
    const res = await fetch(listUrl, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    competencies = data.items || [];
    
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

        document.addEventListener('DOMContentLoaded', () => {
            fetchCompetencies();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) searchInput.addEventListener('input', applyFilters);
            const filterCategory = document.getElementById('filterCategory');
            if (filterCategory) filterCategory.addEventListener('change', applyFilters);
        });
        window.openFormModal = openFormModal;
        window.closeFormModal = closeFormModal;
        window.viewCompetency = viewCompetency;
        window.editCompetency = editCompetency;
        window.confirmAction = confirmAction;
        window.performDelete = performDelete;
        window.applyFilters = applyFilters;
