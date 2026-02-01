<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Settings - Executive HR</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @vite(['resources/css/app.css', 'resources/css/dashboard/dashboard.css'])
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen">
    @include('partials.admin-sidebar')

    <div class="main-content">
        <main class="p-6 lg:p-12 max-w-[1200px] mx-auto">
            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">System Synchronization</h1>
                <p class="text-slate-500 mt-2 font-medium text-lg">Configure data replication to the central domain.</p>
            </div>

            <!-- Sync Component -->
            @include('partials.sync')
            
        </main>
    </div>
</body>
</html>
