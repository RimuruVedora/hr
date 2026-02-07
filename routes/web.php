<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetencyController;
use App\Http\Controllers\JobRoleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\LearningCourseController;
use App\Http\Controllers\LearningAssessmentController;
use App\Http\Controllers\EmployeeDashboardController;

use App\Http\Controllers\SuccessionPlanController;

// Fallback route to serve build assets if symlink is missing
Route::get('/build/{path}', function ($path) {
    $path = public_path('build/' . $path);
    if (file_exists($path)) {
        $mimeType = \Illuminate\Support\Facades\File::mimeType($path);
        
        // Fix for incorrect MIME type detection on some servers
        if (str_ends_with($path, '.js')) {
            $mimeType = 'application/javascript';
        } elseif (str_ends_with($path, '.css')) {
            $mimeType = 'text/css';
        }

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
    abort(404);
})->where('path', '.*');

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

// Temporary Setup Route (Generate Sync Token)
Route::get('/debug-config', function () {
    $deptFile = base_path('app/Models/Department.php');
    $deptContent = file_exists($deptFile) ? file_get_contents($deptFile) : 'File not found';
    
    $syncFile = base_path('app/Http/Controllers/SyncController.php');
    $syncContent = file_exists($syncFile) ? file_get_contents($syncFile) : 'File not found';

    return response()->json([
        'base_path' => base_path(),
        'department_model_content' => $deptContent,
        'sync_controller_content_snippet' => substr($syncContent, 0, 500) . '...' . substr($syncContent, -500),
        'department_has_fillable' => strpos($deptContent, "'name'") !== false,
        'sync_has_employee_logic' => strpos($syncContent, 'Employee::where') !== false
    ]);
});

Route::get('/setup/generate-token', function () {
    $admin = App\Models\Account::where('Account_Type', 1)->first();
    if (!$admin) {
        return "Error: No Admin account found.";
    }
    $token = $admin->createToken('SyncToken')->plainTextToken;
    return response()->json(['token' => $token]);
});

Route::middleware(['auth'])->group(function () {
    // Keep Alive Route
    Route::post('/keep-alive', function () {
        return response()->json(['status' => 'ok']);
    })->name('keep-alive');

    // API Tester Route
    Route::get('/api-tester', function () {
        return view('api_tester');
    })->name('api.tester');

    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/send-otp', [App\Http\Controllers\ProfileController::class, 'sendPasswordOtp'])->name('profile.send-otp');
    Route::post('/profile/update-password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/update-picture', [App\Http\Controllers\ProfileController::class, 'updatePicture'])->name('profile.update-picture');

    Route::get('/admin/dashboard', function () {
        return view('dashboard.admin-dashboard');
    })->name('admin.dashboard');

    // Sync Routes
    Route::get('/admin/sync', [App\Http\Controllers\SyncController::class, 'index'])->name('sync.index');
    Route::post('/admin/sync/settings', [App\Http\Controllers\SyncController::class, 'updateSettings'])->name('sync.update');
    Route::post('/admin/sync/now', [App\Http\Controllers\SyncController::class, 'syncNow'])->name('sync.now');

    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');

    Route::get('/user-management', [UserManagementController::class, 'index'])->name('user.management');
    Route::get('/api/users', [UserManagementController::class, 'getUsers'])->name('api.users');
    Route::get('/api/logs', [UserManagementController::class, 'getLogs'])->name('api.logs');

    Route::get('/competency/framework', [CompetencyController::class, 'main'])->name('competency.framework');
    Route::get('/competency/mapping', [CompetencyController::class, 'mapping'])->name('competency.mapping');
    Route::get('/competency/analytics', [CompetencyController::class, 'analytics'])->name('competency.analytics');
    Route::get('/competency/analytics-data', [CompetencyController::class, 'analyticsData'])->name('competency.analytics-data');
    Route::get('/competency/ai-plan/{id}', [CompetencyController::class, 'generateAIPlan'])->name('competency.ai-plan');
    Route::post('/competency/ai-chat/{id}', [CompetencyController::class, 'chatAI'])->name('competency.ai-chat');
    Route::get('/competency/list', [CompetencyController::class, 'indexJson'])->name('competency.list');
    Route::post('/competency', [CompetencyController::class, 'store'])->name('competency.store');
    Route::put('/competency/{id}', [CompetencyController::class, 'update'])->name('competency.update');
    Route::delete('/competency/{id}', [CompetencyController::class, 'destroy'])->name('competency.destroy');

    // Job Role / Mapping Routes
    Route::get('/job-roles', [JobRoleController::class, 'index'])->name('job-roles.index');
    Route::post('/job-roles', [JobRoleController::class, 'store'])->name('job-roles.store');
    Route::put('/job-roles/{id}', [JobRoleController::class, 'update'])->name('job-roles.update');
    Route::delete('/job-roles/{id}', [JobRoleController::class, 'destroy'])->name('job-roles.destroy');

    // Training Routes
    Route::get('/training/schedule', [App\Http\Controllers\TrainingController::class, 'index'])->name('training.schedule');
    Route::get('/training/evaluation', [App\Http\Controllers\TrainingController::class, 'evaluation'])->name('training.evaluation');
    Route::get('/training/{id}/participants', [App\Http\Controllers\TrainingController::class, 'getParticipants'])->name('training.participants');
    Route::post('/training/grade', [App\Http\Controllers\TrainingController::class, 'updateGrade'])->name('training.grade.update');
    Route::post('/training/schedule', [App\Http\Controllers\TrainingController::class, 'store'])->name('training.store');
    Route::post('/training/{id}/start', [App\Http\Controllers\TrainingController::class, 'start'])->name('training.start');
    Route::post('/training/send-otp', [App\Http\Controllers\TrainingController::class, 'sendOtp'])->name('training.send-otp');
    Route::post('/training/{id}/enroll', [App\Http\Controllers\TrainingController::class, 'enroll'])->name('training.enroll');

    // Learning Routes (Courses)
    Route::get('/learning/courses', [LearningCourseController::class, 'index'])->name('learning.courses');
    Route::post('/learning/courses', [LearningCourseController::class, 'store'])->name('learning.courses.store');
    Route::delete('/learning/courses/{id}', [LearningCourseController::class, 'destroy'])->name('learning.courses.destroy');
    Route::patch('/learning/courses/{id}/status', [LearningCourseController::class, 'updateStatus'])->name('learning.courses.status');

    // Learning Routes (Assessments)
    Route::get('/learning/my-assessments', [LearningAssessmentController::class, 'employeeAssessments'])->name('learning.employee.assessments');
    Route::get('/learning/exams', [App\Http\Controllers\EmployeeExamController::class, 'index'])->name('employee.exams');
    Route::get('/learning/exam/start/{trainingId}', [App\Http\Controllers\EmployeeExamController::class, 'start'])->name('exam.start');
    Route::get('/learning/exam/take/{attemptId}', [App\Http\Controllers\EmployeeExamController::class, 'take'])->name('exam.take');
    Route::post('/learning/exam/submit/{attemptId}', [App\Http\Controllers\EmployeeExamController::class, 'submit'])->name('exam.submit');

    Route::get('/learning/assessments', [LearningAssessmentController::class, 'index'])->name('learning.assessments');
    Route::post('/learning/assessments', [LearningAssessmentController::class, 'store'])->name('learning.assessments.store');
    Route::delete('/learning/assessments/{id}', [LearningAssessmentController::class, 'destroy'])->name('learning.assessments.destroy');
    Route::get('/learning/assessment-scores', [LearningAssessmentController::class, 'scores'])->name('learning.assessment-scores');
    
    // Overall Score Routes
    Route::get('/learning/overall-score', [LearningAssessmentController::class, 'overallScore'])->name('learning.overall-score');
    Route::get('/learning/courses/{id}/trainings', [LearningAssessmentController::class, 'getCourseTrainings'])->name('learning.course.trainings');
    Route::get('/learning/trainings/{id}/scores', [LearningAssessmentController::class, 'getTrainingScores'])->name('learning.training.scores');

    // Succession Planning Routes
    Route::get('/succession-plans', [SuccessionPlanController::class, 'index'])->name('succession.plans');
    Route::post('/succession-plans', [SuccessionPlanController::class, 'store'])->name('succession.plans.store');
    Route::get('/talent-assessment', [SuccessionPlanController::class, 'talentAssessment'])->name('talent.assessment');

    // Employee Self-Service (ESS) Routes
    Route::get('/ess/request', [App\Http\Controllers\EssRequestController::class, 'index'])->name('ess.request');
    Route::post('/ess/request', [App\Http\Controllers\EssRequestController::class, 'store'])->name('ess.request.store');
    Route::put('/ess/request/{id}', [App\Http\Controllers\EssRequestController::class, 'update'])->name('ess.request.update');
    Route::get('/ess/request/{id}/download', [App\Http\Controllers\EssRequestController::class, 'downloadResponse'])->name('ess.request.download');
});
