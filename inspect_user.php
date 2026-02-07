<?php

use App\Models\Account;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = 6;
$email = 'duarte09488@gmail.com';

echo "--- Inspecting Account ID: $id ---\n";
$accountById = Account::find($id);
if ($accountById) {
    echo "Found by ID:\n";
    $emailVal = $accountById->email; // Lowercase based on print_r
    echo "Email Value: '" . $emailVal . "'\n";
    echo "Email Length: " . strlen($emailVal) . "\n";
    echo "Hex Dump: " . bin2hex($emailVal) . "\n";
    
    // Clean it
    $cleanEmail = trim($emailVal);
    if ($cleanEmail !== $emailVal) {
        echo "Detected dirty email. Cleaning...\n";
        $accountById->email = $cleanEmail;
        $accountById->save();
        echo "Cleaned email saved.\n";
    } else {
        echo "Email appears clean (trim didn't change it).\n";
        // Check for internal whitespace or weird chars not caught by trim?
    }
} else {
    echo "Not found by ID.\n";
}

echo "\n--- Inspecting Account by Email: '$email' ---\n";
$accountByEmail = Account::where('Email', $email)->first();
if ($accountByEmail) {
    echo "Found by Email (exact match):\n";
    echo "ID: " . $accountByEmail->Login_ID . "\n";
} else {
    echo "Not found by Email (exact match).\n";
    
    // Try like search
    $accountLike = Account::where('Email', 'LIKE', "%$email%")->first();
    if ($accountLike) {
        echo "Found by LIKE search:\n";
        echo "ID: " . $accountLike->Login_ID . "\n";
        echo "Actual Email: '" . $accountLike->Email . "'\n";
        echo "Actual Email Length: " . strlen($accountLike->Email) . "\n";
        echo "Hex Dump: " . bin2hex($accountLike->Email) . "\n";
    }
}
