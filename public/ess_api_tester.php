<?php
// Check if it's a POST request (handling the form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $url = 'https://hr2.viahale.com/api/ess/request'; // Target Live URL
    // $url = 'http://localhost/hr/public/api/ess/request'; // Localhost fallback

    $token = 'ess_secret_token_98765'; // Token matching .env
    $method = $_POST['method'] ?? 'GET';

    // Get JSON input from the frontend fetch
    $inputData = json_decode(file_get_contents('php://input'), true);
    // If inputData is null, maybe it came from regular form post (not expected for this tester logic but just in case)
    if (!$inputData && $method === 'POST') {
         // handle error
    }

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        // Exclude 'method' from the payload sent to API
        unset($inputData['method']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inputData));
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    
    // Disable SSL verification for testing purposes
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
    <title>ESS API Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-4xl mx-auto space-y-8">
        <h1 class="text-3xl font-bold text-gray-800 text-center">ESS API Tester</h1>

        <!-- Configuration -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4 text-gray-700">Configuration</h2>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 text-blue-700">
                <p><strong>Target URL:</strong> <code id="targetUrl">https://hr2.viahale.com/api/ess/request</code></p>
                <p><strong>Token:</strong> <code>ess_secret_token_98765</code></p>
            </div>
        </div>

        <!-- POST Tester -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4 text-green-700">POST: Send Data (Create Request)</h2>
            <form id="postForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">User ID (e.g., EMP001)</label>
                        <input type="text" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-gray-50 p-2 border" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-gray-50 p-2 border">
                            <option value="Leave">Leave</option>
                            <option value="Overtime">Overtime</option>
                            <option value="Document">Document</option>
                            <option value="Complaint">Complaint</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <input type="text" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-gray-50 p-2 border" placeholder="e.g. Sick Leave">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Details (JSON)</label>
                    <textarea id="details" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-gray-50 p-2 border" placeholder='{"reason": "Fever", "dates": ["2023-10-27"]}'></textarea>
                    <p class="text-xs text-gray-500 mt-1">Leave empty or enter valid JSON (e.g., {"reason": "sick"}).</p>
                </div>
                <button type="button" onclick="sendPostRequest()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                    Send POST Request
                </button>
            </form>
            <div id="postResponse" class="mt-4 p-4 bg-gray-50 rounded border hidden overflow-auto max-h-60 text-sm font-mono"></div>
        </div>

        <!-- GET Tester -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4 text-blue-700">GET: Retrieve Data</h2>
            <p class="mb-4 text-gray-600">Fetches all ESS requests from the system.</p>
            <button type="button" onclick="sendGetRequest()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                Send GET Request
            </button>
            <div id="getResponse" class="mt-4 p-4 bg-gray-50 rounded border hidden overflow-auto max-h-60 text-sm font-mono"></div>
        </div>

    </div>

    <script>
        async function sendPostRequest() {
            const userId = document.getElementById('user_id').value;
            const category = document.getElementById('category').value;
            const type = document.getElementById('type').value;
            let details = {};
            
            try {
                const detailsText = document.getElementById('details').value.trim();
                if (detailsText) {
                    details = JSON.parse(detailsText);
                }
            } catch (e) {
                alert('Invalid JSON in details field. Please correct it or leave it empty.');
                return;
            }

            const payload = {
                user_id: userId,
                category: category,
                type: type,
                details: details,
                method: 'POST'
            };

            const responseDiv = document.getElementById('postResponse');
            responseDiv.classList.remove('hidden');
            responseDiv.innerHTML = 'Sending...';

            try {
                const res = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                responseDiv.innerHTML = JSON.stringify(data, null, 2);
            } catch (err) {
                responseDiv.innerHTML = 'Error: ' + err.message;
            }
        }

        async function sendGetRequest() {
            const responseDiv = document.getElementById('getResponse');
            responseDiv.classList.remove('hidden');
            responseDiv.innerHTML = 'Fetching...';

            try {
                const res = await fetch('', {
                    method: 'POST', // We use POST to self to proxy the GET request
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ method: 'GET' })
                });
                const data = await res.json();
                responseDiv.innerHTML = JSON.stringify(data, null, 2);
            } catch (err) {
                responseDiv.innerHTML = 'Error: ' + err.message;
            }
        }
    </script>
</body>
</html>
