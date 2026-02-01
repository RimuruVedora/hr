<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\SyncController;
use App\Models\SyncSetting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sync:run', function () {
    $settings = SyncSetting::first();
    if ($settings && $settings->sync_mode === 'auto') {
        $this->info('Starting Auto Sync...');
        
        $result = SyncController::executeSync();
        
        if ($result['success']) {
            $this->info('Sync Successful: ' . $result['message']);
        } else {
            $this->error('Sync Failed: ' . $result['message']);
        }
    } else {
        $this->info('Auto sync is disabled or settings not found.');
    }
})->purpose('Run data synchronization if auto mode is enabled');

// Schedule the command
Schedule::command('sync:run')->everyMinute();
