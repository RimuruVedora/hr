<?php
/**
 * Helper script to create symlinks on shared hosting.
 * Upload this file to your public_html folder (where your index.php is) and visit it in your browser.
 * e.g., https://hr2.viahale.com/symlink.php
 */

$targetBuild = '/home/hr2.viahale.com/hr/public/build';
$linkBuild = __DIR__ . '/build';

$targetStorage = '/home/hr2.viahale.com/hr/public/storage';
$linkStorage = __DIR__ . '/storage';

echo "<h1>Symlink Creator</h1>";

// Create Build Symlink
if (file_exists($linkBuild)) {
    echo "<p style='color:orange'>Build link already exists.</p>";
} else {
    if (symlink($targetBuild, $linkBuild)) {
        echo "<p style='color:green'>Success: Created symlink for 'build' directory.</p>";
    } else {
        echo "<p style='color:red'>Error: Failed to create symlink for 'build'. Check permissions or paths.</p>";
        echo "Target: $targetBuild<br>Link: $linkBuild<br>";
    }
}

// Create Storage Symlink
if (file_exists($linkStorage)) {
    echo "<p style='color:orange'>Storage link already exists.</p>";
} else {
    if (symlink($targetStorage, $linkStorage)) {
        echo "<p style='color:green'>Success: Created symlink for 'storage' directory.</p>";
    } else {
        echo "<p style='color:red'>Error: Failed to create symlink for 'storage'. Check permissions or paths.</p>";
        echo "Target: $targetStorage<br>Link: $linkStorage<br>";
    }
}

echo "<hr><p>After seeing success messages, delete this file and try reloading your site.</p>";
