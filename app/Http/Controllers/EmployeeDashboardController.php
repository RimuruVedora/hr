<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\TrainingParticipant;
use App\Models\EmployeeCompetency;
use App\Models\Training;
use App\Models\EmployeeAssessment;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('email', $user->email)->first();

        if (!$employee) {
            // Handle case where account exists but no matching employee record
            return view('dashboard.Employee-dashboard', [
                'employee' => null,
                'stats' => ['courses' => 0, 'ongoing' => 0, 'skills' => 0],
                'activities' => [],
                'recentActivities' => [],
                'skillGapData' => ['labels' => [], 'current' => [], 'target' => []]
            ]);
        }

        // Stats
        $ongoingTrainingsCount = TrainingParticipant::where('employee_id', $employee->id)
            ->whereHas('training', function($q) {
                $q->whereIn('status', ['published', 'in_progress']);
            })
            ->count();

        // Assuming 'Courses' refers to completed trainings or distinct courses enrolled
        $completedTrainingsCount = TrainingParticipant::where('employee_id', $employee->id)
            ->whereHas('training', function($q) {
                $q->where('status', 'completed');
            })
            ->count();

        $acquiredSkillsCount = EmployeeCompetency::where('employee_id', $employee->id)
            ->whereColumn('current_proficiency', '>=', 'target_proficiency')
            ->count();

        // Activities (To Do / Ongoing)
        $activities = TrainingParticipant::where('employee_id', $employee->id)
            ->with(['training.course'])
            ->whereHas('training', function($q) {
                $q->whereIn('status', ['published', 'in_progress']);
            })
            ->get()
            ->map(function($participant) use ($employee) {
                $training = $participant->training;
                
                // Check if exam is completed
                $isExamCompleted = false;
                if ($training->assessment_id) {
                    $isExamCompleted = EmployeeAssessment::where('employee_id', $employee->id)
                        ->where('training_id', $training->id)
                        ->whereIn('status', ['passed', 'failed', 'completed'])
                        ->exists();
                }

                return [
                    'id' => $training->id,
                    'title' => $training->title,
                    'type' => 'Training',
                    'date' => $training->start_date->format('M d, Y'),
                    'status' => $participant->status ?? 'Pending',
                    'description' => $training->description,
                    'is_exam_completed' => $isExamCompleted,
                    'has_exam' => !is_null($training->assessment_id)
                ];
            });

        // Recent Activities (Completed)
        $recentActivities = TrainingParticipant::where('employee_id', $employee->id)
            ->with(['training.course'])
            ->whereHas('training', function($q) {
                $q->where('status', 'completed');
            })
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($participant) {
                return [
                    'id' => $participant->training->id,
                    'title' => $participant->training->title,
                    'type' => 'Completed Training',
                    'date' => $participant->training->end_date->format('M d, Y'),
                    'status' => 'Completed',
                ];
            });

        // Skill Gap Data
        $competencies = EmployeeCompetency::where('employee_id', $employee->id)
            ->with('competency')
            ->get();
        
        $skillGapData = [
            'labels' => $competencies->pluck('competency.name')->toArray(),
            'current' => $competencies->pluck('current_proficiency')->map(fn($v) => $this->mapProficiencyToScore($v))->toArray(),
            'target' => $competencies->pluck('target_proficiency')->map(fn($v) => $this->mapProficiencyToScore($v))->toArray(),
        ];

        return view('dashboard.Employee-dashboard', [
            'employee' => $employee,
            'stats' => [
                'courses' => $completedTrainingsCount,
                'ongoing' => $ongoingTrainingsCount,
                'skills' => $acquiredSkillsCount
            ],
            'activities' => $activities,
            'recentActivities' => $recentActivities,
            'skillGapData' => $skillGapData
        ]);
    }

    private function mapProficiencyToScore($level) {
        if (is_numeric($level)) {
            return (float) $level;
        }
        $map = [
            'beginner' => 1,
            'intermediate' => 2,
            'advanced' => 3,
            'expert' => 4,
            'master' => 5
        ];
        return $map[strtolower($level ?? '')] ?? 0;
    }
}
