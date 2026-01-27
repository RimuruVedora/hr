
        // Initialize Data
        let users = [];
        let logs = [];

        async function fetchUsers() {
            try {
                // Use injected route or fallback to relative path (fallback might fail in subdirs)
                const url = (window.APP_ROUTES && window.APP_ROUTES.users) ? window.APP_ROUTES.users : '/api/users';
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                const data = await res.json();
                // Map 'accounts' table fields to UI fields
                users = data.map(u => ({
                    id: u.Login_ID || u.id || 'N/A',
                    name: u.email || 'Unknown', // Using email as name since Name isn't in Account
                    position: u.Account_Type || 'User',
                    status: 'Active' // Default to Active as no status column in Account
                }));
                renderUsers();
                updateStats();
            } catch (e) {
                console.error('Failed to fetch users', e);
            }
        }

        async function fetchLogs() {
            try {
                const url = (window.APP_ROUTES && window.APP_ROUTES.logs) ? window.APP_ROUTES.logs : '/api/logs';
                const res = await fetch(url);
                const data = await res.json();
                logs = data.map(l => ({
                    time: new Date(l.created_at).toLocaleString(),
                    name: l.user_id || 'System',
                    action: l.action,
                    meta: l.ip_address || 'N/A'
                }));
                renderLogs();
            } catch (e) {
                console.error('Failed to fetch logs', e);
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            fetchUsers();
            fetchLogs();
        });

        let currentUserEditing = null;

        function updateStats() {
            const active = users.filter(u => u.status === 'Active').length;
            const inactive = users.filter(u => u.status === 'Inactive').length;
            document.getElementById('count-total').innerText = users.length;
            document.getElementById('count-active').innerText = active;
            document.getElementById('count-inactive').innerText = inactive;
        }

        function renderUsers() {
            const body = document.getElementById('user-table-body');
            body.innerHTML = users.map(user => `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs text-slate-500 font-bold">${user.id}</td>
                    <td class="px-6 py-4 font-bold text-slate-800">${user.name}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">${user.position}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full text-[10px] font-bold uppercase border ${
                            user.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                        }">
                            <span class="w-1.5 h-1.5 rounded-full ${user.status === 'Active' ? 'bg-emerald-600' : 'bg-rose-600'}"></span>
                            ${user.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="openEditModal('${user.id}')" class="text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg text-xs font-bold transition-all border border-transparent hover:border-blue-100">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Status
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderLogs() {
            const body = document.getElementById('log-table-body');
            body.innerHTML = logs.map(log => `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4 text-xs font-medium text-slate-400">${log.time}</td>
                    <td class="px-6 py-4 font-bold text-slate-700 text-sm">${log.name}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold ${log.action.includes('Success') ? 'text-emerald-600' : 'text-slate-500'}">
                            ${log.action}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400 italic">${log.meta}</td>
                </tr>
            `).join('');
        }

        function switchTab(tab) {
            const userView = document.getElementById('view-users');
            const logView = document.getElementById('view-logs');
            const userTab = document.getElementById('tab-users');
            const logTab = document.getElementById('tab-logs');

            if (tab === 'users') {
                userView.classList.remove('hidden');
                logView.classList.add('hidden');
                userTab.classList.add('tab-active');
                logTab.classList.remove('tab-active');
            } else {
                userView.classList.add('hidden');
                logView.classList.remove('hidden');
                userTab.classList.remove('tab-active');
                logTab.classList.add('tab-active');
            }
        }

        function openEditModal(id) {
            currentUserEditing = users.find(u => u.id === id);
            if (!currentUserEditing) return;
            
            document.getElementById('modal-user-name').innerText = currentUserEditing.name;
            document.getElementById('statusSelect').value = currentUserEditing.status;
            
            const overlay = document.getElementById('editModalOverlay');
            const content = document.getElementById('editModalContent');
            
            const iconDiv = document.getElementById('modal-status-icon');
            if(currentUserEditing.status === 'Active') {
                iconDiv.className = "w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl bg-emerald-50 text-emerald-600";
            } else {
                iconDiv.className = "w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl bg-rose-50 text-rose-600";
            }

            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.style.opacity = '1';
                content.style.opacity = '1';
                content.style.transform = 'scale(1)';
            }, 10);
        }

        function closeEditModal() {
            const overlay = document.getElementById('editModalOverlay');
            const content = document.getElementById('editModalContent');
            
            overlay.style.opacity = '0';
            content.style.opacity = '0';
            content.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }

        function saveUserChanges() {
            const newStatus = document.getElementById('statusSelect').value;
            // Here you would typically send a PUT request to the backend
            // For now we just update the local array
            if(currentUserEditing) {
                currentUserEditing.status = newStatus;
                renderUsers();
                updateStats();
                closeEditModal();
            }
        }

        // Export functions to global scope
        window.switchTab = switchTab;
        window.openEditModal = openEditModal;
        window.closeEditModal = closeEditModal;
        window.saveUserChanges = saveUserChanges;
