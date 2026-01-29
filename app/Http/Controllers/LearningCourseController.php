<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Competency;
use Illuminate\Support\Facades\Storage;

class LearningCourseController extends Controller
{
    public function index()
    {
        $departments = DB::table('departments')->select('id', 'name')->get();
        // Eager load competencies to display in the grid if needed
        $courses = Course::with('competencies', 'department')->orderBy('created_at', 'desc')->get();
        // Fetch all competencies for the search dropdown
        $competenciesMaster = Competency::pluck('name', 'id');

        return view('learning.admin-learning-exam-creation', compact('departments', 'courses', 'competenciesMaster'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'level' => 'required|in:organization,department,management,team',
            'department_id' => 'nullable|exists:departments,id',
            'category' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'description' => 'required|string',
            'competencies' => 'nullable|array', // Array of competency IDs or names (if creating new?)
            'picture' => 'nullable|image|max:2048', // 2MB Max
            'material_pdf' => 'nullable|mimes:pdf|max:10240', // 10MB Max
        ]);

        $course = new Course($validated);
        $course->status = 'draft';

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('courses/pictures', 'public');
            $course->picture = $path;
        }

        if ($request->hasFile('material_pdf')) {
            $path = $request->file('material_pdf')->store('courses/materials', 'public');
            $course->material_pdf = $path;
        }

        $course->save();

        if (!empty($validated['competencies'])) {
            // Assuming competencies are sent as names, we need to find their IDs
            // Or better, update frontend to send IDs.
            // For now, let's assume the frontend sends names and we look them up.
            // If the frontend can be updated to send IDs, that's better.
            // Let's assume names for now as per current JS logic, but map them to IDs.
            
            $compIds = [];
            foreach ($validated['competencies'] as $compName) {
                $comp = Competency::where('name', $compName)->first();
                if ($comp) {
                    $compIds[] = $comp->id;
                }
            }
            $course->competencies()->attach($compIds);
        }

        return response()->json(['success' => true, 'course' => $course->load('competencies')], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->status = $request->input('status'); // 'published' or 'draft'
        $course->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        // Delete files
        if ($course->picture) {
            Storage::disk('public')->delete($course->picture);
        }
        if ($course->material_pdf) {
            Storage::disk('public')->delete($course->material_pdf);
        }
        $course->competencies()->detach();
        $course->delete();

        return response()->json(['success' => true]);
    }
}
