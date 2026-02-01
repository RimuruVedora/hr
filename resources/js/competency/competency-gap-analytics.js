
let performanceData = [];
let gapChart = null;
let summaryChart = null;
let currentEmployeeId = null;

// Expose functions to global scope for HTML onclick events
window.filterTable = filterTable;
window.viewAnalysis = viewAnalysis;
window.closeModal = closeModal;
window.triggerAIPlan = triggerAIPlan;
window.sendChatMessage = sendChatMessage;

document.addEventListener('DOMContentLoaded', async () => {
    // Attach event listener for AI Plan button
    const aiPlanBtn = document.getElementById('btn-trigger-ai-plan');
    if (aiPlanBtn) {
        aiPlanBtn.addEventListener('click', triggerAIPlan);
    }

    // Attach enter key for chat input
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendChatMessage();
        });
    }

    try {
        const url = (window.COMPETENCY_API && window.COMPETENCY_API.analytics) ? window.COMPETENCY_API.analytics : '/competency/analytics-data';
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch data');
        const rawData = await response.json();
        
        // Pre-calculate gaps and achievement stats
        performanceData = rawData.map(emp => {
            let totalGap = 0;
            let totalRequired = 0;
            let totalCurrentCapped = 0;

            if (emp.requiredSet && emp.competencies) {
                emp.requiredSet.forEach((req, index) => {
                    const curr = emp.competencies[index] || 0;
                    totalGap += Math.max(0, req - curr);
                    totalRequired += req;
                    totalCurrentCapped += Math.min(curr, req);
                });
            }
            
            return { 
                ...emp, 
                totalGap,
                achievedScore: totalRequired > 0 ? ((totalCurrentCapped / totalRequired) * 100).toFixed(0) : 100
            };
        });

        renderPerformance();
    } catch (error) {
        console.error('Error loading analytics:', error);
        // Maybe render empty state or error message
        document.getElementById('performance-table-body').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-slate-500">No data available or failed to load.</td></tr>';
    }
});

function renderPerformance() {
    const body = document.getElementById('performance-table-body');
    document.getElementById('stat-total-emp').innerText = performanceData.length;

    if (performanceData.length === 0) {
        body.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-slate-500">No records found.</td></tr>';
        return;
    }

    body.innerHTML = performanceData.map(emp => {
        const hasGap = emp.totalGap > 0;
        const gapValue = emp.totalGap.toFixed(1);
        
        return `
        <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="px-6 py-4">
                <p class="font-bold text-slate-800">${emp.name}</p>
                <p class="text-[10px] text-slate-400 font-mono">${emp.id}</p>
            </td>
            <td class="px-6 py-4">
                <p class="text-sm font-semibold text-slate-700">${emp.role}</p>
                <p class="text-xs text-slate-400">${emp.dept}</p>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">${emp.current}</span>
                    <i class="fa-solid fa-arrow-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">${emp.required}</span>
                </div>
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-[10px] font-black uppercase border ${
                    !hasGap ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                }">
                    ${!hasGap ? '<i class="fa-solid fa-check-double"></i> Met' : `<i class="fa-solid fa-triangle-exclamation"></i> Gap (-${gapValue})`}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="text-[10px] font-bold ${
                    emp.priority === 'Critical' ? 'text-rose-600' : emp.priority === 'High' ? 'text-amber-600' : 'text-slate-400'
                }">
                    <i class="fa-solid fa-circle text-[6px] mr-1.5 align-middle"></i>${emp.priority}
                </span>
            </td>
            <td class="px-6 py-4 text-right">
                <button onclick="viewAnalysis('${emp.id}')" class="bg-white border border-slate-200 text-slate-700 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all shadow-sm">
                    <i class="fa-solid fa-chart-line mr-1.5"></i> View
                </button>
            </td>
        </tr>
    `}).join('');

    initSummaryChart();
}

function initSummaryChart() {
    const ctx = document.getElementById('summaryLineChart').getContext('2d');
    if (summaryChart) summaryChart.destroy();

    // Dynamically get departments from data
    const depts = [...new Set(performanceData.map(d => d.dept))].filter(d => d);
    if (depts.length === 0) depts.push('General');

    const hitData = depts.map(d => performanceData.filter(e => e.dept === d && e.totalGap === 0).length);
    const missData = depts.map(d => performanceData.filter(e => e.dept === d && e.totalGap > 0).length);

    summaryChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: depts,
            datasets: [
                {
                    label: 'Hit Required Targets',
                    data: hitData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8
                },
                {
                    label: 'Won\'t Hit / Gaps',
                    data: missData,
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1, color: '#94a3b8' },
                    grid: { color: '#f1f5f9' }
                },
                x: { 
                    ticks: { color: '#64748b', font: { weight: '600' } },
                    grid: { display: false } 
                }
            }
        }
    });
}

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const dept = document.getElementById('deptFilter').value;
    const rows = document.querySelectorAll('#performance-table-body tr');

    rows.forEach((row, index) => {
        const data = performanceData[index];
        const matchesSearch = data.name.toLowerCase().includes(search);
        const matchesDept = dept === "" || data.dept === dept;
        row.style.display = matchesSearch && matchesDept ? "" : "none";
    });
}

function viewAnalysis(id) {
    currentEmployeeId = id;
    const emp = performanceData.find(e => e.id === id);
    document.getElementById('modalEmpName').innerText = emp.name;
    document.getElementById('modalEmpRole').innerText = `${emp.role} • ${emp.dept}`;
    
    document.getElementById('modalAchieved').innerText = emp.achievedScore + '%';
    document.getElementById('modalGap').innerText = emp.totalGap > 0 ? `-${emp.totalGap.toFixed(1)} Points` : 'No Gap';
    
    // Reset AI Content
    document.getElementById('aiPlanContent').innerHTML = '<p class="italic text-slate-400">Click "Generate Plan" to get AI-powered recommendations based on competency gaps.</p>';

    // Hide Chat Section and Reset Chat
    document.getElementById('aiChatSection').classList.add('hidden');
    document.getElementById('chatHistory').innerHTML = `
        <div class="flex items-start gap-2.5">
            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs"><i class="fa-solid fa-robot"></i></div>
            <div class="bg-white border border-slate-200 p-2.5 rounded-lg rounded-tl-none shadow-sm text-slate-600 max-w-[85%]">
                Hi! I've analyzed the competency profile. Feel free to ask me for more details on any specific action item.
            </div>
        </div>
    `;
    const chatInput = document.getElementById('chatInput');
    if (chatInput) chatInput.value = '';

    toggleModal(true);
    setTimeout(() => initIndividualChart(emp), 300);
}

async function triggerAIPlan() {
    if (!currentEmployeeId) return;
    
    const container = document.getElementById('aiPlanContent');
    container.innerHTML = '<div class="flex items-center gap-2 text-indigo-600"><i class="fa-solid fa-circle-notch fa-spin"></i> Generating personalized plan...</div>';
    
    try {
        const baseUrl = (window.COMPETENCY_API && window.COMPETENCY_API.plan) ? window.COMPETENCY_API.plan : '/competency/ai-plan/:id';
        const url = baseUrl.replace(':id', currentEmployeeId);
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to generate plan');
        const plan = await response.json();
        
        let html = `<p class="font-semibold text-slate-800 mb-3">${plan.summary}</p>`;
        
        if (plan.actions && plan.actions.length > 0) {
            html += '<div class="space-y-3">';
            plan.actions.forEach(action => {
                if (typeof action === 'string') {
                    html += `<div class="flex items-start gap-2"><i class="fa-solid fa-check text-indigo-500 mt-1"></i><span>${action}</span></div>`;
                } else {
                    html += `
                        <div class="bg-white p-3 rounded-lg border border-indigo-100 shadow-sm">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-indigo-700 text-xs uppercase">${action.skill}</span>
                                <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 rounded">${action.gap}</span>
                            </div>
                            <ul class="text-xs text-slate-600 space-y-1 mt-2">
                                ${action.suggestions.map(s => `<li class="flex items-start gap-1.5"><i class="fa-solid fa-angle-right text-indigo-400 mt-0.5"></i>${s}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }
            });
            html += '</div>';
        }
        
        container.innerHTML = html;
        
        // Show Chat Section
        document.getElementById('aiChatSection').classList.remove('hidden');
        
    } catch (error) {
        console.error('AI Plan Error:', error);
        container.innerHTML = '<p class="text-rose-500 text-xs font-bold">Failed to generate plan. Please try again.</p>';
    }
}

async function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message || !currentEmployeeId) return;

    // Append User Message
    const chatHistory = document.getElementById('chatHistory');
    chatHistory.innerHTML += `
        <div class="flex items-start gap-2.5 justify-end">
            <div class="bg-indigo-600 text-white p-2.5 rounded-lg rounded-tr-none shadow-sm max-w-[85%]">
                ${message}
            </div>
            <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs"><i class="fa-solid fa-user"></i></div>
        </div>
    `;
    input.value = '';
    chatHistory.scrollTop = chatHistory.scrollHeight;

    // Show Loading
    const loadingId = 'loading-' + Date.now();
    chatHistory.innerHTML += `
        <div id="${loadingId}" class="flex items-start gap-2.5">
            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs"><i class="fa-solid fa-robot"></i></div>
            <div class="bg-white border border-slate-200 p-2.5 rounded-lg rounded-tl-none shadow-sm text-slate-600">
                <i class="fa-solid fa-circle-notch fa-spin"></i> Thinking...
            </div>
        </div>
    `;
    chatHistory.scrollTop = chatHistory.scrollHeight;

    try {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        const baseUrl = (window.COMPETENCY_API && window.COMPETENCY_API.chat) ? window.COMPETENCY_API.chat : '/competency/ai-chat/:id';
        const url = baseUrl.replace(':id', currentEmployeeId);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ message })
        });
        
        const data = await response.json();
        
        // Remove Loading
        const loader = document.getElementById(loadingId);
        if (loader) loader.remove();

        // Append AI Response
        if (data.reply) {
             chatHistory.innerHTML += `
                <div class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs"><i class="fa-solid fa-robot"></i></div>
                    <div class="bg-white border border-slate-200 p-2.5 rounded-lg rounded-tl-none shadow-sm text-slate-600 max-w-[85%]">
                        ${data.reply.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
        } else {
             throw new Error('No reply');
        }

    } catch (error) {
        console.error('Chat Error:', error);
        const loader = document.getElementById(loadingId);
        if (loader) loader.remove();
        
        chatHistory.innerHTML += `
            <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-xs"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="bg-white border border-rose-200 p-2.5 rounded-lg rounded-tl-none shadow-sm text-rose-600 text-xs">
                    Failed to send message. Please try again.
                </div>
            </div>
        `;
    }
    chatHistory.scrollTop = chatHistory.scrollHeight;
}

function initIndividualChart(emp) {
    const ctx = document.getElementById('gapChart').getContext('2d');
    if (gapChart) gapChart.destroy();
    
    // Use dynamic labels from backend if available
    const labels = emp.labels && emp.labels.length > 0 ? emp.labels : ['Technical', 'Leadership', 'Behavioral', 'Strategic', 'Execution'];
    
    gapChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'Current Status', data: emp.competencies, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.4 },
                { label: 'Required Status', data: emp.requiredSet, borderColor: '#94a3b8', borderDash: [5, 5], tension: 0.4 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { max: 5 } } }
    });
}

function toggleModal(show) {
    const overlay = document.getElementById('viewModalOverlay');
    const content = document.getElementById('viewModalContent');
    if (show) {
        overlay.classList.remove('hidden');
        setTimeout(() => { overlay.style.opacity = '1'; content.style.opacity = '1'; content.style.transform = 'scale(1)'; }, 10);
    } else {
        overlay.style.opacity = '0'; content.style.opacity = '0'; content.style.transform = 'scale(0.95)';
        setTimeout(() => overlay.classList.add('hidden'), 300);
    }
}

function closeModal() { toggleModal(false); }
