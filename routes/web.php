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

// Diagnostic Route for Login Issues (Bypassing 404 on public files)
Route::any('/debug-login', function () {
    $email = request('email');
    $id = request('id');
    $checkPass = request('password');
    $fix = request('fix');
    $newEmail = request('new_email');
    $targetId = request('target_id');

    echo "<h1>Login Debugger (Route)</h1>";
    echo "<form method='GET'>
        Email: <input type='text' name='email' value='" . htmlspecialchars($email ?? '') . "'><br>
        OR ID: <input type='text' name='id' value='" . htmlspecialchars($id ?? '') . "'><br>
        Test Password: <input type='text' name='password' value='" . htmlspecialchars($checkPass ?? '') . "'><br>
        <button type='submit'>Check</button>
    </form>";

    if ($email || $id) {
        echo "<hr>";
        $query = App\Models\Account::query();
        if ($id) {
            $query->where('Login_ID', $id);
        } else {
            // Try exact match first
            $query->where('Email', $email);
            // If not found, try like
            if ($query->count() == 0) {
                $query = App\Models\Account::where('Email', 'LIKE', "%$email%");
            }
        }
        
        $users = $query->get();
        
        if ($users->count() == 0) {
            echo "No user found.";
        } else {
            foreach ($users as $user) {
                // Handle Update Action
                if (request()->isMethod('post') && $targetId == $user->Login_ID && $newEmail !== null) {
                    $user->Email = trim($newEmail);
                    $user->save();
                    echo "<div style='color:green; font-weight:bold; border:1px solid green; padding:10px; margin:10px 0;'>
                            ✅ Email updated to: " . htmlspecialchars($user->Email) . "
                          </div>";
                }

                echo "<h3>User Found (ID: {$user->Login_ID})</h3>";
                echo "<ul>";
                echo "<li><strong>Stored Email (Raw):</strong> [" . $user->Email . "] (Length: " . strlen($user->Email) . ")</li>";
                echo "<li><strong>Trimmed Email:</strong> [" . trim($user->Email) . "]</li>";
                echo "<li><strong>Account Type:</strong> {$user->Account_Type}</li>";
                echo "<li><strong>Position:</strong> {$user->position}</li>";
                // Handle missing columns gracefully
                $status = $user->Status ?? 'N/A';
                $active = $user->active ?? 'N/A';
                echo "<li><strong>Status:</strong> {$status} (Active: {$active})</li>";
                echo "</ul>";
                
                // Update Email Form
                echo "<div style='background:#f3f4f6; padding:15px; border-radius:8px; margin-top:10px;'>";
                echo "<strong>Update Email Address:</strong>";
                echo "<form method='POST' action='?id=" . $id . "&email=" . urlencode($email) . "'>
                        <input type='hidden' name='_token' value='" . csrf_token() . "'>
                        <input type='hidden' name='target_id' value='" . $user->Login_ID . "'>
                        <div style='display:flex; gap:10px; margin-top:5px;'>
                            <input type='text' name='new_email' value='" . htmlspecialchars($user->Email ?? '') . "' style='padding:5px; width:300px;' placeholder='Enter correct email'>
                            <button type='submit' style='padding:5px 15px; background:blue; color:white; border:none; cursor:pointer;'>Save New Email</button>
                        </div>
                      </form>";
                echo "</div>";

                if ($checkPass) {
                    echo "<h4>Password Check</h4>";
                    if (Illuminate\Support\Facades\Hash::check($checkPass, $user->Password)) {
                        echo "<div style='color:green'>✅ Password MATCHES!</div>";
                    } else {
                        echo "<div style='color:red'>❌ Password DOES NOT match.</div>";
                        // Security: Don't show hash in production unless absolutely necessary
                        // echo "Hash: " . $user->Password; 
                    }
                }
                
                // Fix Option
                if (trim($user->Email) !== $user->Email) {
                    echo "<br><div style='color:orange'>⚠️ Warning: Email has whitespace!</div>";
                    echo "<form method='POST' action='?email=" . urlencode($email) . "&id=" . $user->Login_ID . "&fix=true'>
                            <input type='hidden' name='_token' value='" . csrf_token() . "'> 
                            <button type='submit'>Fix Whitespace</button>
                          </form>";
                    
                    if ($fix && request()->isMethod('post') && !$newEmail) {
                        $user->Email = trim($user->Email);
                        $user->save();
                        echo "<div style='color:green'>✅ Email trimmed and saved! Refresh to verify.</div>";
                    }
                }
            }
        }
    }
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
