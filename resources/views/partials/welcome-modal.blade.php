@if(session('show_welcome_modal'))
<div id="welcome-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md transition-all duration-500 opacity-0 invisible">
    <div class="relative w-full max-w-lg mx-4 transform transition-all duration-500 scale-90" id="welcome-modal-content">
        <!-- Decorative background elements -->
        <div class="absolute -top-20 -left-20 w-64 h-64 bg-blue-500/30 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-purple-500/30 rounded-full blur-3xl animate-pulse delay-700"></div>

        <!-- Modal Card -->
        <div class="relative bg-white/90 backdrop-blur-xl rounded-[40px] shadow-2xl overflow-hidden border border-white/50">
            <!-- Header Pattern -->
            <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-r from-blue-600 to-indigo-600">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-white/90 to-transparent"></div>
            </div>

            <div class="relative px-8 pt-12 pb-8 text-center">
                <!-- Profile Picture Container -->
                @php
                    $imageData = Auth::user()->path_img ?? null;
                @endphp
                <div class="relative mx-auto mb-6 w-28 h-28 group">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full blur opacity-40 group-hover:opacity-60 transition-opacity duration-300"></div>
                    <div class="relative w-28 h-28 rounded-full p-1 bg-white shadow-xl ring-4 ring-white/50">
                        @if($imageData)
                            <img src="data:image/jpeg;base64,{{ base64_encode($imageData) }}"
                                 alt="Profile"
                                 class="w-full h-full rounded-full object-cover border-2 border-slate-100">
                        @else
                            <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name ?? 'User') . '&background=random&color=fff&background=3b82f6' }}"
                                 alt="Profile"
                                 class="w-full h-full rounded-full object-cover border-2 border-slate-100">
                        @endif
                    </div>
                    <!-- Status Indicator -->
                    <div class="absolute bottom-2 right-2 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full shadow-md animate-bounce-subtle"></div>
                </div>

                <!-- User Info -->
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">
                        Welcome back, <br>
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                            {{ Auth::user()->name ?? 'User' }}
                        </span>
                        <span class="inline-block animate-wave origin-bottom-right">👋</span>
                    </h2>
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-slate-100 border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                        <p class="text-slate-600 font-semibold text-sm tracking-wide uppercase">{{ Auth::user()->position ?? 'Team Member' }}</p>
                    </div>
                </div>

                <!-- Welcome Message Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 mb-8 border border-blue-100/50 shadow-inner relative overflow-hidden group hover:shadow-md transition-shadow duration-300">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-blue-200/20 rounded-bl-full -mr-4 -mt-4 transition-transform duration-500 group-hover:scale-110"></div>
                    <p class="relative text-slate-700 text-sm leading-relaxed font-medium">
                        "Ready to make an impact today? Your dashboard is updated with the latest insights and performance metrics."
                    </p>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('profile') }}" class="group relative flex items-center justify-center py-4 px-6 rounded-2xl bg-white text-slate-700 font-bold border-2 border-slate-100 hover:border-blue-200 hover:bg-blue-50/50 transition-all duration-300">
                        <span class="mr-2 group-hover:-translate-x-1 transition-transform duration-300">👤</span>
                        View Profile
                    </a>
                    <button onclick="closeWelcomeModal()" class="group relative flex items-center justify-center py-4 px-6 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-[1.02] transition-all duration-300">
                        <span>Dashboard</span>
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }
    .animate-wave {
        animation: wave 1.5s infinite;
    }
    .animate-bounce-subtle {
        animation: bounce-subtle 2s infinite;
    }
    @keyframes bounce-subtle {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('welcome-modal');
        const modalContent = document.getElementById('welcome-modal-content');
        
        if(modal) {
            // Prevent scrolling on body
            document.body.style.overflow = 'hidden';
            
            // Show modal with sequence
            setTimeout(() => {
                modal.classList.remove('opacity-0', 'invisible');
                setTimeout(() => {
                    modalContent.classList.remove('scale-90');
                    modalContent.classList.add('scale-100');
                }, 100);
            }, 100);
        }
    });

    function closeWelcomeModal() {
        const modal = document.getElementById('welcome-modal');
        const modalContent = document.getElementById('welcome-modal-content');
        
        // Restore scrolling
        document.body.style.overflow = '';
        
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-90', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('invisible');
            }, 300);
        }, 100);
    }
</script>
@endif
