<div x-data="syncSettings()" x-init="initData()" class="bg-white rounded-[28px] border border-slate-100 shadow-sm p-8">
    <div class="flex justify-between items-start mb-8">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Data Synchronization</h2>
            <p class="text-slate-500 font-medium mt-1">Manage data sync between Local and Domain environments.</p>
        </div>
        <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600">
            <ion-icon name="sync-outline" class="text-2xl"></ion-icon>
        </div>
    </div>

    <!-- Sync Mode Toggle -->
    <div class="mb-8">
        <label class="block text-sm font-bold text-slate-700 uppercase tracking-wide mb-3">Sync Mode</label>
        <div class="flex items-center space-x-4">
            <button @click="setMode('manual')" 
                :class="mode === 'manual' ? 'bg-indigo-600 text-white shadow-md ring-2 ring-indigo-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                class="px-6 py-3 rounded-xl font-bold transition-all duration-200 flex items-center gap-2">
                <ion-icon name="hand-left-outline"></ion-icon>
                Manual Sync
            </button>
            <button @click="setMode('auto')" 
                :class="mode === 'auto' ? 'bg-indigo-600 text-white shadow-md ring-2 ring-indigo-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                class="px-6 py-3 rounded-xl font-bold transition-all duration-200 flex items-center gap-2">
                <ion-icon name="infinite-outline"></ion-icon>
                Auto Sync
            </button>
        </div>
        <p class="text-xs text-slate-400 mt-2 font-medium" x-show="mode === 'auto'">
            <ion-icon name="information-circle-outline" class="align-middle"></ion-icon> 
            Data will be synced automatically in the background.
        </p>
        <p class="text-xs text-slate-400 mt-2 font-medium" x-show="mode === 'manual'">
            <ion-icon name="information-circle-outline" class="align-middle"></ion-icon> 
            Data is synced only when you click "Sync Now".
        </p>
    </div>

    <!-- Configuration -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Remote Domain URL</label>
            <input type="url" x-model="remoteUrl" placeholder="https://hr2.viahale.com" 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">API Token</label>
            <input type="password" x-model="apiToken" placeholder="Enter remote API token" 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between pt-6 border-t border-slate-100">
        <button @click="saveSettings" :disabled="isSaving"
            class="text-slate-500 hover:text-indigo-600 font-bold text-sm transition-colors disabled:opacity-50">
            <span x-text="isSaving ? 'Saving...' : 'Save Configuration'"></span>
        </button>

        <button x-show="mode === 'manual'" @click="syncNow" :disabled="isSyncing || !remoteUrl || !apiToken"
            class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
            <ion-icon name="refresh-outline" :class="{'animate-spin': isSyncing}"></ion-icon>
            <span x-text="isSyncing ? 'Syncing Data...' : 'Sync Now'"></span>
        </button>
    </div>

    <!-- Logs/Status -->
    <div class="mt-8 bg-slate-50 rounded-xl p-4 border border-slate-100" x-show="lastSyncDate">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Last Sync Status</h3>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="lastSyncStatus === 'success' ? 'bg-emerald-500' : 'bg-red-500'"></span>
                <span class="text-sm font-bold text-slate-700" x-text="lastSyncMessage"></span>
            </div>
            <span class="text-xs text-slate-400 font-mono" x-text="lastSyncDate"></span>
        </div>
    </div>

</div>

<script>
    function syncSettings() {
        return {
            mode: 'manual',
            remoteUrl: '',
            apiToken: '',
            isSaving: false,
            isSyncing: false,
            lastSyncDate: null,
            lastSyncStatus: null,
            lastSyncMessage: null,

            initData() {
                // Initialize with data passed from backend
                const settings = @json($settings ?? null);
                if (settings) {
                    this.mode = settings.sync_mode;
                    this.remoteUrl = settings.remote_url;
                    this.apiToken = settings.api_token;
                    this.lastSyncDate = settings.last_synced_at;
                    this.lastSyncStatus = settings.last_sync_status;
                    this.lastSyncMessage = settings.last_sync_message;
                }
            },

            setMode(newMode) {
                this.mode = newMode;
                // Auto-save when switching mode
                // this.saveSettings(); 
            },

            async saveSettings() {
                this.isSaving = true;
                try {
                    const response = await fetch('{{ route("sync.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            sync_mode: this.mode,
                            remote_url: this.remoteUrl,
                            api_token: this.apiToken
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        // alert('Settings saved.');
                    } else {
                        alert('Error saving settings.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Network error.');
                } finally {
                    this.isSaving = false;
                }
            },

            async syncNow() {
                this.isSyncing = true;
                // First save settings to ensure latest URL/Token are used
                await this.saveSettings();
                
                try {
                    const response = await fetch('{{ route("sync.now") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    
                    this.lastSyncDate = new Date().toISOString(); // Update locally or fetch fresh
                    this.lastSyncStatus = data.success ? 'success' : 'failed';
                    this.lastSyncMessage = data.message;
                    
                    if (!data.success) {
                        alert('Sync Failed: ' + data.message);
                    }
                } catch (e) {
                    console.error(e);
                    this.lastSyncStatus = 'failed';
                    this.lastSyncMessage = 'Network Error';
                } finally {
                    this.isSyncing = false;
                }
            }
        }
    }
</script>
