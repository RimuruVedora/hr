<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\JobRole;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\EmployeeCompetency;
use App\Models\TrainingParticipant;
use App\Models\EmployeeAssessment;
use App\Models\SuccessionPlan;

class SuccessionPlanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'target_role_id' => 'required|exists:job_roles,id',
            'department_id' => 'required|exists:departments,id',
            'readiness' => 'required|string',
        ]);

        foreach ($request->employee_ids as $employeeId) {
            SuccessionPlan::create([
                'employee_id' => $employeeId,
                'target_role_id' => $request->target_role_id,
                'department_id' => $request->department_id,
                'readiness' => $request->readiness,
                'status' => 'Pending', // Default status
            ]);
        }

        return redirect()->route('succession.plans')->with('success', 'Succession plan created successfully.');
    }

    public function talentAssessment()
    {
        // 1. Fetch Top Skilled Employees (Sum of current_proficiency)
        $topSkilled = Employee::with('jobRole')
            ->withSum('competencies as total_proficiency', 'current_proficiency')
            ->orderByDesc('total_proficiency')
            ->take(5)
            ->get();

        // 2. Fetch Employees with Most Passed Exams
        $topExamPassers = Employee::with('jobRole')
            ->withCount(['employeeAssessments as passed_exams_count' => function ($query) {
                $query->where('status', 'passed');
            }])
            ->orderByDesc('passed_exams_count')
            ->take(5)
            ->get();

        // 3. Fetch Employees with Most Completed Trainings
        $topTrainingCompleters = Employee::with('jobRole')
            ->withCount(['trainingParticipants as completed_trainings_count' => function ($query) {
                $query->whereIn('status', ['completed', 'enrolled']); // Including enrolled for now as data might be sparse
            }])
            ->orderByDesc('completed_trainings_count')
            ->take(5)
            ->get();
            
        // Dashboard Cards Data
        $totalEmployees = Employee::count();
        $avgCompetencyScore = EmployeeCompetency::avg('current_proficiency') ?? 0;
        $totalPassedExams = EmployeeAssessment::where('status', 'passed')->count();
        $totalCompletedTrainings = TrainingParticipant::where('status', 'completed')->count();

        return view('succession_planning.talent-assessment', compact(
            'topSkilled',
            'topExamPassers',
            'topTrainingCompleters',
            'totalEmployees',
            'avgCompetencyScore',
            'totalPassedExams',
            'totalCompletedTrainings'
        ));
    }

    public function index()
    {
        // Dashboard Cards Data
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'Active')->count();
        $completedAssessments = 0; // Placeholder, assuming relationship or status check needed
        $pendingPlans = 0; // Placeholder

        // Data for Tab 1: Available Positions
        // For now, we'll list Job Roles that might need succession
        $availablePositions = JobRole::all()->map(function($role) {
            return [
                'id' => $role->id,
                'position' => $role->name,
                'department' => 'N/A', // Job Roles might not be directly tied to department in schema yet
                'current_employee' => 'Vacant', // Placeholder
                'priority' => 'High', // Placeholder
            ];
        });

        // Data for Tab 2: Succession Table
        // Placeholder data
        $successionPlans = []; 

        // Data for Create Plan Form
        $departments = Department::all();
        $jobRoles = JobRole::all();
        $employees = Employee::with('jobRole')->where('status', 'Active')->get();

        return view('succession-plan', compact(
            'totalEmployees', 
            'activeEmployees', 
            'completedAssessments', 
            'pendingPlans',
            'availablePositions',
            'successionPlans',
            'departments',
            'jobRoles',
            'employees'
        ));
    }
}
