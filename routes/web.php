<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\UserManagementController;

// Explicit GET handlers to avoid 405 on some Apache/XAMPP setups
Route::get('/', function () {
    return redirect('/login');
});
// Handle direct hits to /public (when DocumentRoot isn't set to /public)
Route::get('/public', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard.admin-dashboard');
    })->name('admin.dashboard');

    Route::get('/user-management', [UserManagementController::class, 'index'])->name('user.management');
    Route::get('/api/users', [UserManagementController::class, 'getUsers'])->name('api.users');
    Route::get('/api/logs', [UserManagementController::class, 'getLogs'])->name('api.logs');

    Route::get('/competency/framework', [CompetencyController::class, 'main'])->name('competency.framework');
    Route::get('/competency/mapping', [CompetencyController::class, 'mapping'])->name('competency.mapping');
    Route::get('/competency/analytics', [CompetencyController::class, 'analytics'])->name('competency.analytics');
    Route::get('/competency/analytics-data', [CompetencyController::class, 'analyticsData'])->name('competency.analytics-data');
    Route::get('/competency/list', [CompetencyController::class, 'indexJson'])->name('competency.list');
    Route::post('/competency', [CompetencyController::class, 'store'])->name('competency.store');
    Route::put('/competency/{id}', [CompetencyController::class, 'update'])->name('competency.update');
    Route::delete('/competency/{id}', [CompetencyController::class, 'destroy'])->name('competency.destroy');

    // Job Role / Mapping Routes
    Route::get('/job-roles', [JobRoleController::class, 'index'])->name('job-roles.index');
    Route::post('/job-roles', [JobRoleController::class, 'store'])->name('job-roles.store');
    Route::put('/job-roles/{id}', [JobRoleController::class, 'update'])->name('job-roles.update');
    Route::delete('/job-roles/{id}', [JobRoleController::class, 'destroy'])->name('job-roles.destroy');
});
