<?php
// Place this in public/debug_login.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$email = $_GET['email'] ?? '';
$id = $_GET['id'] ?? '';
$checkPass = $_GET['password'] ?? '';
$fix = $_GET['fix'] ?? false;

echo "<h1>Login Debugger</h1>";
echo "<form method='GET'>
    Email: <input type='text' name='email' value='" . htmlspecialchars($email) . "'><br>
    OR ID: <input type='text' name='id' value='" . htmlspecialchars($id) . "'><br>
    Test Password: <input type='text' name='password' value='" . htmlspecialchars($checkPass) . "'><br>
    <button type='submit'>Check</button>
</form>";

if ($email || $id) {
    echo "<hr>";
    $query = Account::query();
    if ($id) {
        $query->where('Login_ID', $id);
    } else {
        // Try exact match first
        $query->where('Email', $email);
        // If not found, try like
        if ($query->count() == 0) {
            $query = Account::where('Email', 'LIKE', "%$email%");
        }
    }
    
    $users = $query->get();
    
    if ($users->count() == 0) {
        echo "No user found.";
    } else {
        foreach ($users as $user) {
            echo "<h3>User Found (ID: {$user->Login_ID})</h3>";
            echo "<ul>";
            echo "<li><strong>Stored Email (Raw):</strong> [" . $user->Email . "] (Length: " . strlen($user->Email) . ")</li>";
            echo "<li><strong>Trimmed Email:</strong> [" . trim($user->Email) . "]</li>";
            echo "<li><strong>Account Type:</strong> {$user->Account_Type}</li>";
            echo "<li><strong>Position:</strong> {$user->position}</li>";
            echo "<li><strong>Status:</strong> {$user->Status} (Active: {$user->active})</li>";
            echo "</ul>";
            
            if ($checkPass) {
                echo "<h4>Password Check</h4>";
                if (Hash::check($checkPass, $user->Password)) {
                    echo "<div style='color:green'>✅ Password MATCHES!</div>";
                } else {
                    echo "<div style='color:red'>❌ Password DOES NOT match.</div>";
                    echo "Hash: " . $user->Password;
                }
            }
            
            // Fix Option
            if (trim($user->Email) !== $user->Email) {
                echo "<br><div style='color:orange'>⚠️ Warning: Email has whitespace!</div>";
                echo "<form method='POST' action='?email=" . urlencode($email) . "&id=" . $user->Login_ID . "&fix=true'>
                        <input type='hidden' name='_token' value='" . csrf_token() . "'> 
                        <button type='submit'>Fix Whitespace</button>
                      </form>"; // Note: POST handling needs full route setup, simplified here using GET for quick fix if needed or just code below
                
                if ($fix) {
                    $user->Email = trim($user->Email);
                    $user->save();
                    echo "<div style='color:green'>✅ Email trimmed and saved! Refresh to verify.</div>";
                }
            }
        }
    }
}
