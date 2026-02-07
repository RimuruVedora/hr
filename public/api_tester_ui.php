<?php
// Check if it's a POST request (handling the form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Get JSON input from the frontend fetch
    $inputData = json_decode(file_get_contents('php://input'), true);

    $target = $inputData['target_env'] ?? 'live';
    
    if ($target === 'local') {
        $url = 'http://localhost/hr/public/api/employee/sync';
    } else {
        $url = 'https://hr2.viahale.com/api/employee/sync';
    }

    // Remove target_env from payload before forwarding
    if (isset($inputData['target_env'])) {
        unset($inputData['target_env']);
    }

    $token = 'secret_token_12345'; // Token matching .env

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inputData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    
    // Disable SSL verification for testing purposes (optional)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode(['success' => false, 'message' => 'cURL Error: ' . $error]);
    } else {
        // Pass through the response code and body
        http_response_code($httpCode);
        echo $response;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee API Tester UI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Employee API Tester UI</h1>
        
        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-700">
            <p class="font-bold">Mode:</p>
            <p>This UI proxies requests to the selected server.</p>
        </div>

        <form id="apiForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Target Environment</label>
                <select name="target_env" id="target_env" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border">
                    <option value="live">Live (hr2.viahale.com)</option>
                    <option value="local">Localhost (localhost/hr/public)</option>
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Employee ID</label>
                    <input type="text" name="employee_id" id="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Job Role</label>
                    <input type="text" name="job_role" id="job_role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border" placeholder="e.g. Software Engineer">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" name="department" id="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border" placeholder="e.g. IT">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="text" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2 border" value="securepass123">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <button type="button" onclick="generateRandomData()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    Generate Random Data
                </button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    <span id="btnText">Send Request</span>
                    <svg id="spinner" class="animate-spin ml-2 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>

        <div id="resultArea" class="mt-6 hidden">
            <h3 class="text-lg font-medium text-gray-900">Response:</h3>
            <pre id="responseOutput" class="mt-2 bg-gray-800 text-green-400 p-4 rounded overflow-auto text-sm"></pre>
        </div>
    </div>

    <script>
        function generateRandomData() {
            const id = Math.floor(Math.random() * 9000) + 1000;
            document.getElementById('employee_id').value = 'EMP-' + id;
            document.getElementById('first_name').value = 'Test';
            document.getElementById('last_name').value = 'User ' + id;
            document.getElementById('email').value = 'test.user.' + id + '@example.com';
            document.getElementById('job_role').value = 'Developer';
            document.getElementById('department').value = 'Engineering';
        }

        document.getElementById('apiForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('spinner');
            const resultArea = document.getElementById('resultArea');
            const responseOutput = document.getElementById('responseOutput');

            // Show loading state
            btnText.textContent = 'Sending...';
            spinner.classList.remove('hidden');
            resultArea.classList.add('hidden');

            // Gather form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                // Send to SAME FILE (acts as proxy)
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                // Display result
                responseOutput.textContent = JSON.stringify(result, null, 2);
                resultArea.classList.remove('hidden');
                
                if (result.success) {
                    responseOutput.classList.remove('text-red-400');
                    responseOutput.classList.add('text-green-400');
                } else {
                    responseOutput.classList.remove('text-green-400');
                    responseOutput.classList.add('text-red-400');
                }

            } catch (error) {
                resultArea.classList.remove('hidden');
                responseOutput.textContent = 'Error: ' + error.message;
                responseOutput.classList.remove('text-green-400');
                responseOutput.classList.add('text-red-400');
            } finally {
                btnText.textContent = 'Send Request';
                spinner.classList.add('hidden');
            }
        });
        
        // Initial random data
        generateRandomData();
    </script>
</body>
</html>
