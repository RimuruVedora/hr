<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive HR Insights Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite('resources/css/dashboard/dashboard.css')
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen">
    @include('partials.admin-sidebar')

    <div class="main-content">
        <main class="p-6 lg:p-12 max-w-[1600px] mx-auto">
            <!-- Dashboard Intro -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <div class="flex items-center space-x-3 mb-4">            </div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">Executive Dashboard</h1>
                    <p class="text-slate-500 mt-2 font-medium text-lg">Workforce strategic oversight and development analytics.</p>
                </div>
              
                
            </div>

            <!-- KPI Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- KPI 1 -->
                <div class="bg-white p-6 rounded-[28px] border border-slate-100 card-hover">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-2xl bg-blue-50 text-blue-600">
                            <i class="fas fa-award text-xl"></i>
                        </div>
                        <div class="flex items-center text-emerald-600 text-xs font-bold bg-emerald-50 px-2 py-1 rounded-lg">
                            <i class="fas fa-arrow-up mr-1"></i> 2.4%
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Skill Proficiency</p>
                    <h3 class="text-3xl font-black mt-1 text-slate-800">78.4%</h3>
                </div>
                <!-- KPI 2 -->
                <div class="bg-white p-6 rounded-[28px] border border-slate-100 card-hover">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600">
                            <i class="fas fa-book-open text-xl"></i>
                        </div>
                        <div class="flex items-center text-emerald-600 text-xs font-bold bg-emerald-50 px-2 py-1 rounded-lg">
                            <i class="fas fa-arrow-up mr-1"></i> 12%
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Active Learners</p>
                    <h3 class="text-3xl font-black mt-1 text-slate-800">1,248</h3>
                </div>
                <!-- KPI 3 -->
                <div class="bg-white p-6 rounded-[28px] border border-slate-100 card-hover">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-2xl bg-purple-50 text-purple-600">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                        <div class="flex items-center text-rose-500 text-xs font-bold bg-rose-50 px-2 py-1 rounded-lg">
                            <i class="fas fa-arrow-down mr-1"></i> 0.02
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Bench Strength</p>
                    <h3 class="text-3xl font-black mt-1 text-slate-800">0.85</h3>
                </div>
                <!-- KPI 4 -->
                <div class="bg-white p-6 rounded-[28px] border border-slate-100 card-hover">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-2xl bg-orange-50 text-orange-600">
                            <i class="fas fa-user-check text-xl"></i>
                        </div>
                        <div class="flex items-center text-emerald-600 text-xs font-bold bg-emerald-50 px-2 py-1 rounded-lg">
                            <i class="fas fa-arrow-up mr-1"></i> 1.5%
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Talent Retention</p>
                    <h3 class="text-3xl font-black mt-1 text-slate-800">94.2%</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Competency Chart -->
                <section class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm lg:col-span-2">
                    <div class="flex justify-between items-center mb-10">
                        <div>
                            <h2 class="font-black text-2xl text-slate-900">Competency Matrix</h2>
                            <p class="text-slate-500 font-medium">Visualizing skill gaps across core domains</p>
                        </div>
                        <div class="flex space-x-4 text-xs font-bold">
                            <div class="flex items-center space-x-1.5 text-slate-600">
                                <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                                <span>Actual</span>
                            </div>
                            <div class="flex items-center space-x-1.5 text-slate-600">
                                <span class="w-3 h-3 rounded-full bg-slate-200"></span>
                                <span>Benchmark</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-8" id="competency-container">
                        <!-- Javascript will inject content here for demo purposes -->
                    </div>
                </section>

                <!-- Learning Engagement -->
                <section class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm">
                    <h2 class="font-black text-2xl text-slate-900 mb-2">Learning Pulse</h2>
                    <p class="text-sm text-slate-500 mb-8 font-medium">Course engagement over last 30 days</p>
                    
                    <div class="space-y-6" id="learning-container">
                        <!-- Trends injected here -->
                    </div>
                </section>

                <!-- Succession Planning Table -->
                <section class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-sm lg:col-span-3">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-10 gap-4">
                        <div>
                            <h2 class="font-black text-2xl text-slate-900">Succession Pipeline</h2>
                            <p class="text-slate-500 font-medium">Critical role candidates and readiness status</p>
                        </div>
                        <div class="flex bg-slate-100 p-1.5 rounded-xl self-start">
                            <button class="px-4 py-2 text-xs font-bold bg-white rounded-lg shadow-sm">High Potential</button>
                            <button class="px-4 py-2 text-xs font-bold text-slate-500">Ready Now</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[11px] text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <th class="pb-6 px-4">Talent Profile</th>
                                    <th class="pb-6">Current Role Fit</th>
                                    <th class="pb-6">Potential</th>
                                    <th class="pb-6">Overall Score</th>
                                    <th class="pb-6 text-right">Readiness</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50" id="succession-table">
                                <!-- Injected rows -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        // Data Configuration
        const competencies = [
            { name: 'Strategic Leadership', actual: 85, target: 90 },
            { name: 'Data Science & AI', actual: 58, target: 80 },
            { name: 'Agile Operations', actual: 92, target: 85 },
            { name: 'Digital Fluency', actual: 74, target: 95 }
        ];

        const learningTrends = [
            { title: 'AI Ethics in HR', status: 'Mandatory', progress: 65, color: '#3b82f6', trend: [10, 30, 25, 45, 60, 65] },
            { title: 'Remote Management', status: 'Core', progress: 88, color: '#10b981', trend: [20, 40, 60, 75, 80, 88] },
            { title: 'Project Scoping', status: 'Optional', progress: 42, color: '#8b5cf6', trend: [5, 10, 15, 20, 35, 42] }
        ];

        const candidates = [
            { name: 'Alex Rivera', role: 'Senior Product Manager', pot: 'High', score: 94, window: 'Ready Now', avatar: 'AR' },
            { name: 'Jordan Smith', role: 'Team Lead Eng', pot: 'High', score: 88, window: '1-2 Years', avatar: 'JS' },
            { name: 'Maria Garcia', role: 'Head of Operations', pot: 'Medium', score: 79, window: '2-3 Years', avatar: 'MG' },
            { name: 'Sam Chen', role: 'Director of Growth', pot: 'High', score: 91, window: 'Ready Now', avatar: 'SC' }
        ];

        // Generator Functions
        function initCompetencies() {
            const container = document.getElementById('competency-container');
            container.innerHTML = competencies.map(c => `
                <div class="group">
                    <div class="flex justify-between items-end mb-3">
                        <span class="text-sm font-black text-slate-800">${c.name}</span>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-bold text-slate-400">vs Target ${c.target}%</span>
                            <span class="text-sm font-black text-blue-600">${c.actual}%</span>
                        </div>
                    </div>
                    <div class="h-5 bg-slate-100 rounded-full relative overflow-hidden">
                        <div class="absolute top-0 bottom-0 border-r-2 border-slate-300 z-10 opacity-60" style="left: ${c.target}%"></div>
                        <div class="h-full rounded-full bg-gradient-to-r ${c.actual >= c.target ? 'from-emerald-400 to-emerald-600' : 'from-blue-500 to-indigo-600'} transition-all duration-1000" style="width: ${c.actual}%"></div>
                    </div>
                </div>
            `).join('');
        }

        function generateSparkline(data, color) {
            const max = Math.max(...data);
            const width = 120;
            const height = 40;
            const points = data.map((d, i) => `${(i / (data.length - 1)) * width},${height - (d / max * height)}`).join(' ');
            return `
                <svg width="${width}" height="${height}" class="overflow-visible">
                    <polyline fill="none" stroke="${color}" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" points="${points}" />
                </svg>
            `;
        }

        function initLearning() {
            const container = document.getElementById('learning-container');
            container.innerHTML = learningTrends.map(l => `
                <div class="p-5 rounded-3xl border border-slate-50 bg-slate-50/40 hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all cursor-default">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h4 class="text-sm font-black text-slate-800 mb-1">${l.title}</h4>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-blue-500 bg-blue-50 px-2 py-0.5 rounded-md">${l.status}</span>
                        </div>
                        ${generateSparkline(l.trend, l.color)}
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold">
                        <span class="text-slate-400">Progress</span>
                        <span class="text-slate-900">${l.progress}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-200 rounded-full mt-2">
                        <div class="h-full bg-slate-800 rounded-full" style="width: ${l.progress}%"></div>
                    </div>
                </div>
            `).join('');
        }

        function initSuccession() {
            const tbody = document.getElementById('succession-table');
            tbody.innerHTML = candidates.map((p, i) => `
                <tr class="group hover:bg-slate-50/80 transition-all cursor-pointer">
                    <td class="py-6 px-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-11 h-11 rounded-2xl bg-slate-100 border-2 border-white shadow-sm flex items-center justify-center text-slate-600 font-black text-sm">
                                ${p.avatar}
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">${p.name}</p>
                                <p class="text-[11px] text-slate-400 font-bold uppercase tracking-tighter">Emp ID: 0092${i}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-6">
                        <p class="text-sm font-bold text-slate-700">${p.role}</p>
                        <div class="flex items-center space-x-1 mt-1.5">
                            ${[1,2,3,4,5].map(s => `<div class="w-1.5 h-1.5 rounded-full ${s <= 4 ? 'bg-amber-400' : 'bg-slate-200'}"></div>`).join('')}
                        </div>
                    </td>
                    <td class="py-6">
                        <span class="text-[10px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest ${p.pot === 'High' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}">
                            ${p.pot}
                        </span>
                    </td>
                    <td class="py-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-slate-800" style="width: ${p.score}%"></div>
                            </div>
                            <span class="text-xs font-black text-slate-900">${p.score}</span>
                        </div>
                    </td>
                    <td class="py-6 text-right">
                        <div class="inline-flex items-center space-x-2 bg-white border border-slate-100 px-4 py-2 rounded-2xl shadow-sm group-hover:border-blue-200 transition-all">
                            <span class="text-xs font-black text-slate-800">${p.window}</span>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-blue-500 transition-colors"></i>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Initialize Everything
        window.onload = () => {
            initCompetencies();
            initLearning();
            initSuccession();
        };
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>