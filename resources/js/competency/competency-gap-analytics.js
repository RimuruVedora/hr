
let performanceData = [];
let gapChart = null;
let summaryChart = null;

// Expose functions to global scope for HTML onclick events
window.filterTable = filterTable;
window.viewAnalysis = viewAnalysis;
window.closeModal = closeModal;

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('/hr/public/competency/analytics-data');
        if (!response.ok) throw new Error('Failed to fetch data');
        performanceData = await response.json();
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
        const hasGap = emp.current < emp.required;
        const gapValue = (emp.required - emp.current).toFixed(1);
        
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

    const hitData = depts.map(d => performanceData.filter(e => e.dept === d && e.current >= e.required).length);
    const missData = depts.map(d => performanceData.filter(e => e.dept === d && e.current < e.required).length);

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
    const emp = performanceData.find(e => e.id === id);
    document.getElementById('modalEmpName').innerText = emp.name;
    document.getElementById('modalEmpRole').innerText = `${emp.role} • ${emp.dept}`;
    const gap = Math.max(0, (emp.required - emp.current)).toFixed(1);
    
    // Avoid division by zero
    const achieved = emp.required > 0 ? ((emp.current / emp.required) * 100).toFixed(0) : 100;
    
    document.getElementById('modalAchieved').innerText = achieved + '%';
    document.getElementById('modalGap').innerText = gap > 0 ? `-${gap} Points` : 'No Gap';
    toggleModal(true);
    setTimeout(() => initIndividualChart(emp), 300);
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
