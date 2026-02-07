<!-- Alpine.js (Required for Session Timeout) -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    [x-cloak] { display: none !important; }
</style>

<div x-data="sessionTimer()" x-init="init()" x-cloak>
    <!-- Hidden Logout Form -->
    <form id="session-timeout-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Modal -->
    <div x-show="showWarning" 
         class="fixed inset-0 z-[9999] overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Session Timeout Warning</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Your session will expire in <span x-text="formattedMinutes"></span>:<span x-text="formattedSeconds"></span> minutes.</p>
                                
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4 overflow-hidden">
                                    <div class="bg-red-600 h-2.5 rounded-full transition-all duration-1000 ease-linear" :style="'width: ' + progress + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" @click="extendSession()" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function sessionTimer() {
        return {
            lifetimeMinutes: {{ config('session.lifetime') }},
            warningThresholdMinutes: 2, 
            timeLeft: 0,
            showWarning: false,
            timer: null,
            endTime: null,

            init() {
                this.resetTimer();
                this.timer = setInterval(() => {
                    this.tick();
                }, 1000);
            },

            resetTimer() {
                this.endTime = Date.now() + (this.lifetimeMinutes * 60 * 1000);
                this.showWarning = false;
            },

            tick() {
                const now = Date.now();
                const remaining = Math.ceil((this.endTime - now) / 1000);
                this.timeLeft = remaining;

                if (remaining <= (this.warningThresholdMinutes * 60) && remaining > 0) {
                    this.showWarning = true;
                } else if (remaining <= 0) {
                    this.logout();
                }
            },

            get formattedMinutes() {
                const m = Math.floor(this.timeLeft / 60);
                return m < 0 ? 0 : m;
            },

            get formattedSeconds() {
                const s = this.timeLeft % 60;
                return (s < 0 ? 0 : s).toString().padStart(2, '0');
            },

            get progress() {
                // Progress from 0% to 100% as time runs out in the warning period
                const warningSeconds = this.warningThresholdMinutes * 60;
                const elapsed = warningSeconds - this.timeLeft;
                const pct = (elapsed / warningSeconds) * 100;
                return Math.min(Math.max(pct, 0), 100);
            },

            extendSession() {
                // Use relative path to avoid CORS/Domain mismatch issues
                fetch('{{ route('keep-alive', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        this.resetTimer();
                    } else {
                        console.error('Session extension failed:', response.status);
                        alert('Failed to extend session. Please refresh the page.');
                    }
                }).catch(error => {
                    console.error('Session extension error:', error);
                    alert('Network error. Please check your connection.');
                });
            },

            logout() {
                const form = document.getElementById('session-timeout-logout-form');
                if (form) {
                    form.submit();
                } else {
                    window.location.href = '/login';
                }
            }
        }
    }
</script>
