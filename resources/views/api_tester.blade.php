<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESS API Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-100 min-h-screen p-8 font-sans text-slate-900">

    <div class="max-w-4xl mx-auto" x-data="apiTester()">
        
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-800">ESS API Tester</h1>
            <p class="text-slate-500 mt-2">Test the ESS Requests API endpoint</p>
        </div>

        <!-- Configuration Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2">Configuration</h2>
            
            <div class="grid grid-cols-1 gap-4">
                <!-- Endpoint Input -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">API Endpoint</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-slate-300 bg-slate-50 text-slate-500 text-sm">
                            GET
                        </span>
                        <input type="text" x-model="url" class="flex-1 block w-full rounded-none rounded-r-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border" placeholder="https://...">
                    </div>
                </div>

                <!-- Token Input -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bearer Token</label>
                    <input type="text" x-model="token" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border" placeholder="Paste your token here">
                    <p class="mt-1 text-xs text-slate-500">Use the token generated from `php artisan generate:token`</p>
                </div>

                <!-- Action Button -->
                <div class="flex justify-end mt-2">
                    <button @click="fetchData" 
                            :disabled="isLoading"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isLoading ? 'Fetching...' : 'Send Request'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Response Area -->
        <div class="bg-white rounded-xl shadow-lg p-6 min-h-[300px]">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-lg font-semibold">Response</h2>
                <div x-show="status" 
                     :class="status >= 200 && status < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                     class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                    Status: <span x-text="status"></span> <span x-text="statusText"></span>
                </div>
            </div>

            <div x-show="!response && !error" class="text-center py-12 text-slate-400">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="mt-2">No data loaded yet. Click "Send Request" to test.</p>
            </div>

            <div x-show="error" x-cloak class="p-4 rounded-md bg-red-50 border border-red-200 text-red-700 mb-4">
                <strong class="font-bold">Error:</strong> <span x-text="error"></span>
            </div>

            <div x-show="response" x-cloak class="relative">
                 <button @click="copyToClipboard" class="absolute top-2 right-2 text-xs bg-slate-200 hover:bg-slate-300 text-slate-600 px-2 py-1 rounded transition">
                    Copy JSON
                </button>
                <pre class="bg-slate-800 text-green-400 p-4 rounded-lg overflow-x-auto text-sm font-mono leading-relaxed" x-text="formattedResponse"></pre>
            </div>
        </div>

    </div>

    <script>
        function apiTester() {
            return {
                url: '{{ url("/api/ess/requests") }}', // Dynamically generated full URL
                token: '1|si3ilWX7aaV5uGi1dWJp3W7ksoN9wMOkncHT3yrg87558e62', // Pre-filled for convenience
                isLoading: false,
                response: null,
                status: null,
                statusText: null,
                error: null,

                get formattedResponse() {
                    return this.response ? JSON.stringify(this.response, null, 2) : '';
                },

                async fetchData() {
                    this.isLoading = true;
                    this.response = null;
                    this.error = null;
                    this.status = null;
                    this.statusText = null;

                    try {
                        const res = await fetch(this.url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });

                        this.status = res.status;
                        this.statusText = res.statusText;

                        const data = await res.json();
                        this.response = data;
                        
                        if (!res.ok) {
                            this.error = data.message || 'Request failed';
                        }

                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.isLoading = false;
                    }
                },

                copyToClipboard() {
                    navigator.clipboard.writeText(JSON.stringify(this.response, null, 2));
                    alert('JSON copied to clipboard!');
                }
            }
        }
    </script>
</body>
</html>
