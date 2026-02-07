<?php
// Simple script to check the content of Department.php on the server
$file = __DIR__ . '/../app/Models/Department.php';

echo "<h1>File Check: app/Models/Department.php</h1>";

if (file_exists($file)) {
    echo "<p style='color:green'>File exists.</p>";
    echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc;'>" . htmlspecialchars(file_get_contents($file)) . "</pre>";
    
    $content = file_get_contents($file);
    if (strpos($content, "'name'") !== false && strpos($content, '$fillable') !== false) {
        echo "<h2 style='color:green'>SUCCESS: \$fillable = ['name'] IS present!</h2>";
    } else {
        echo "<h2 style='color:red'>FAILURE: \$fillable = ['name'] IS MISSING!</h2>";
        echo "<p>Please run <code>git pull</code> on the server.</p>";
    }
} else {
    echo "<p style='color:red'>File not found at $file</p>";
}
?>
