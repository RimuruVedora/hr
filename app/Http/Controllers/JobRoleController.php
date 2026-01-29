<?php

namespace App\Http\Controllers;

use App\Models\JobRole;
use App\Models\Competency;
use App\Models\Employee;
use App\Models\EmployeeCompetency;
use Illuminate\Http\Request;

class JobRoleController extends Controller
{
    public function index()
    {
        $jobRoles = JobRole::with('competencies')->get();
        return response()->json($jobRoles);
    }

    public function store(Request $request)
    {
        // This could be used to create a new job role if we allowed typing in the dropdown
        // For now, we'll assume it's for saving the assignment (which might update existing or create new)
        // But let's follow standard REST. Store = Create New.
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'competencies' => 'array',
            'competencies.*' => 'exists:competencies,id',
            'weighting' => 'integer|min:1|max:5'
        ]);

        $jobRole = JobRole::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'weighting' => $validated['weighting'] ?? 0
        ]);

        if (!empty($validated['competencies'])) {
            $jobRole->competencies()->sync($validated['competencies']);
            $this->syncEmployeeCompetencies($jobRole, $validated['competencies']);
        }

        return response()->json($jobRole->load('competencies'), 201);
    }

    public function update(Request $request, $id)
    {
        $jobRole = JobRole::findOrFail($id);

        $validated = $request->validate([
            'competencies' => 'array',
            'competencies.*' => 'exists:competencies,id',
            'weighting' => 'integer|min:1|max:5'
        ]);

        if (isset($validated['weighting'])) {
            $jobRole->weighting = $validated['weighting'];
            $jobRole->save();
        }

        if (isset($validated['competencies'])) {
            $jobRole->competencies()->sync($validated['competencies']);
            $this->syncEmployeeCompetencies($jobRole, $validated['competencies']);
        }

        return response()->json($jobRole->load('competencies'));
    }

    private function syncEmployeeCompetencies($jobRole, $competencyIds)
    {
        // 1. Find all employees with this job role
        $employees = Employee::where('job_role_id', $jobRole->id)->get();

        if ($employees->isEmpty()) {
            return;
        }

        // 2. Get the full competency objects to access 'proficiency' field
        $competencies = Competency::whereIn('id', $competencyIds)->get();

        foreach ($employees as $employee) {
            
            // Ensure ID is linked if missing (auto-fix)
            if (!$employee->job_role_id) {
                $employee->job_role_id = $jobRole->id;
                $employee->save();
            }

            foreach ($competencies as $competency) {
                // Map proficiency string to numeric score
                $targetScore = $this->mapProficiencyToScore($competency->proficiency);

                // Update or Create the employee competency record
                EmployeeCompetency::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'competency_id' => $competency->id
                    ],
                    [
                        'target_proficiency' => $targetScore,
                    ]
                );
            }
        }
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

    public function destroy($id)
    {
        JobRole::destroy($id);
        return response()->json(null, 204);
    }
}
