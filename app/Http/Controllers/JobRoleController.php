<?php

namespace App\Http\Controllers;

use App\Models\JobRole;
use App\Models\Competency;
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

        // We only update competencies and weighting here based on the 'Assign' modal
        // The modal doesn't seem to allow editing name/description based on previous prompt ("job role(can't edit)"), 
        // but this new prompt says "job roles (dropdown)". 
        // If they pick a job role, we are UPDATING that job role's assignments.

        if (isset($validated['weighting'])) {
            $jobRole->weighting = $validated['weighting'];
            $jobRole->save();
        }

        if (isset($validated['competencies'])) {
            $jobRole->competencies()->sync($validated['competencies']);
        }

        return response()->json($jobRole->load('competencies'));
    }

    public function destroy($id)
    {
        JobRole::destroy($id);
        return response()->json(null, 204);
    }
}
