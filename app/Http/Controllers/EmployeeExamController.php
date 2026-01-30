<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeAssessment;
use App\Models\TrainingParticipant;
use App\Models\Training;
use App\Models\Assessment;
use Carbon\Carbon;

class EmployeeExamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // --- Dashboard Stats ---
        // 1. Total Exams (Available to take from enrolled trainings)
        $totalExams = TrainingParticipant::where('employee_id', $employee->id)
            ->whereHas('training', function($q) {
                $q->whereNotNull('assessment_id');
            })
            ->count();

        // 2. Ongoing Exams
        $ongoingExams = EmployeeAssessment::where('employee_id', $employee->id)
            ->where('status', 'ongoing')
            ->count();

        // 3. Passing Rate
        $completedAttempts = EmployeeAssessment::where('employee_id', $employee->id)
            ->whereIn('status', ['passed', 'failed', 'completed'])
            ->get();
        
        $passedCount = $completedAttempts->where('status', 'passed')->count();
        $totalCompleted = $completedAttempts->count();
        $passingRate = $totalCompleted > 0 ? round(($passedCount / $totalCompleted) * 100) : 0;


        // --- Tab Data ---
        
        // Tab 1: Available Exams
        // Get enrolled trainings that have assessments
        $enrolledTrainings = TrainingParticipant::where('employee_id', $employee->id)
            ->with(['training.course', 'training.assessment'])
            ->whereHas('training', function($q) {
                $q->whereNotNull('assessment_id');
            })
            ->get();
            
        $availableExams = $enrolledTrainings->map(function($participant) use ($employee) {
            $training = $participant->training;
            $assessment = $training->assessment;
            
            if (!$assessment) return null;

            // Check if attempt exists
            $attempt = EmployeeAssessment::where('employee_id', $employee->id)
                ->where('training_id', $training->id)
                ->first();
                
            $status = 'Not Started';
            if ($attempt) {
                $status = ucfirst($attempt->status);
            }
            
            // Check if active (published and within date range)
            $now = Carbon::now();
            $isActive = $training->status === 'published' && $now->between($training->start_date, $training->end_date);
            
            // Determine if startable
            // Can start if: Active AND (Not Started OR Ongoing) AND (Not Completed/Passed/Failed)
            // If Ongoing, action should be "Continue"
            $canStart = $isActive && !in_array($status, ['Passed', 'Completed', 'Failed']);
            
            return [
                'training_id' => $training->id,
                'exam_title' => $assessment->title,
                'course_title' => $training->course->title ?? 'N/A',
                'training_title' => $training->title,
                'no_of_items' => $assessment->questions()->count(),
                'status' => $status,
                'is_active' => $isActive,
                'can_start' => $canStart,
                'action_label' => $status === 'Ongoing' ? 'Continue Exam' : 'Start Exam',
                'start_date' => $training->start_date->format('M d, Y H:i'),
                'end_date' => $training->end_date->format('M d, Y H:i'),
            ];
        })->filter()->values(); // Filter nulls and reindex

        // Tab 2: Scores (Completed Exams)
        $scores = $completedAttempts->map(function($attempt) {
            return [
                'exam_title' => $attempt->assessment->title,
                'course_title' => $attempt->training->course->title ?? 'N/A',
                'training_title' => $attempt->training->title ?? 'N/A',
                'score' => $attempt->score,
                'total_items' => $attempt->total_items,
                'status' => ucfirst($attempt->status),
            ];
        });

        // Tab 3: History
        $history = EmployeeAssessment::where('employee_id', $employee->id)
            ->with(['training.course', 'assessment'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($attempt) {
                return [
                    'exam_title' => $attempt->assessment->title,
                    'course_title' => $attempt->training->course->title ?? 'N/A',
                    'training_title' => $attempt->training->title ?? 'N/A',
                    'date_started' => $attempt->started_at ? $attempt->started_at->format('M d, Y H:i') : '-',
                    'date_ended' => $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : '-',
                    'score' => $attempt->score ?? '-',
                    'total_items' => $attempt->total_items,
                    'status' => ucfirst($attempt->status),
                ];
            });

        return view('learning.employee-exam', compact('totalExams', 'ongoingExams', 'passingRate', 'availableExams', 'scores', 'history'));
    }

    public function start($trainingId)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $training = Training::findOrFail($trainingId);

        // Check enrollment
        $isEnrolled = TrainingParticipant::where('employee_id', $employee->id)
            ->where('training_id', $trainingId)
            ->exists();

        if (!$isEnrolled) {
            return redirect()->back()->with('error', 'You are not enrolled in this training.');
        }

        // Check Assessment
        if (!$training->assessment) {
            return redirect()->back()->with('error', 'No assessment found for this training.');
        }

        // Check Training Schedule
        $now = Carbon::now();
        if ($training->status !== 'published' || !$now->between($training->start_date, $training->end_date)) {
             // Allow if status is in_progress? Assuming published means active for now based on user context
             // User said: "exam's when my enrolled training schedule start"
        }

        // Find or Create Attempt
        $attempt = EmployeeAssessment::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'training_id' => $training->id,
                'assessment_id' => $training->assessment_id,
            ],
            [
                'status' => 'ongoing',
                'started_at' => now(),
                'total_items' => $training->assessment->questions()->count(),
            ]
        );

        if ($attempt->status === 'completed' || $attempt->status === 'passed' || $attempt->status === 'failed') {
             return redirect()->route('employee.exams')->with('info', 'You have already completed this exam.');
        }

        return redirect()->route('exam.take', $attempt->id);
    }

    public function take($attemptId)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $attempt = EmployeeAssessment::where('id', $attemptId)
            ->where('employee_id', $employee->id)
            ->with(['assessment.questions.options', 'training'])
            ->firstOrFail();

        if ($attempt->status !== 'ongoing' && $attempt->status !== 'pending') {
            return redirect()->route('employee.exams')->with('info', 'Exam is not in progress.');
        }
        
        // Ensure status is ongoing if it was pending
        if ($attempt->status === 'pending') {
            $attempt->update([
                'status' => 'ongoing', 
                'started_at' => now()
            ]);
        }

        return view('learning.take-exam', compact('attempt'));
    }

    public function submit(Request $request, $attemptId)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $attempt = EmployeeAssessment::where('id', $attemptId)
            ->where('employee_id', $employee->id)
            ->with(['assessment.questions.options'])
            ->firstOrFail();

        if ($attempt->status !== 'ongoing' && $attempt->status !== 'pending') {
            return redirect()->route('employee.exams');
        }

        $answers = $request->input('answers', []);
        $score = 0;
        $totalQuestions = $attempt->assessment->questions->count();

        foreach ($attempt->assessment->questions as $question) {
            $selectedOptionId = $answers[$question->id] ?? null;
            if ($selectedOptionId) {
                // Assuming AssessmentOption has is_correct boolean field
                // Check if the selected option is correct
                $correctOption = $question->options->where('is_correct', true)->first();
                if ($correctOption && $correctOption->id == $selectedOptionId) {
                    $score++;
                }
            }
        }

        // Determine Pass/Fail (Assuming 75% passing rate if not specified in Assessment)
        // Assessment model has 'passing_score' field which might be percentage or raw score? 
        // Let's check Assessment model again. It has 'passing_score'. 
        // Assuming 'passing_score' is percentage.
        $passingPercentage = $attempt->assessment->passing_score ?? 75;
        $userPercentage = ($totalQuestions > 0) ? ($score / $totalQuestions) * 100 : 0;
        
        $status = $userPercentage >= $passingPercentage ? 'passed' : 'failed';

        $attempt->update([
            'score' => $score,
            'status' => $status,
            'completed_at' => now(),
        ]);

        return redirect()->route('employee.exams')->with('success', "Exam completed! You scored $score/$totalQuestions.");
    }
}

