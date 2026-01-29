<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Competency;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LearningAssessmentController extends Controller
{
    public function index()
    {
        $assessments = Assessment::with(['course', 'competencies', 'questions'])->get();
        $courses = Course::all();
        $competencies = Competency::all();

        // Transform data for the frontend JS
        $exams = $assessments->map(function ($assessment) {
            // Derive scope and proficiency from competencies or course
            // This is a simplification; you might want to adjust logic based on your actual business rules
            $scope = 'internal'; // Default
            $proficiency = 'beginner'; // Default
            
            if ($assessment->competencies->isNotEmpty()) {
                $scope = $assessment->competencies->first()->scope ?? 'internal';
                $proficiency = $assessment->competencies->first()->proficiency ?? 'beginner';
            }

            return [
                'id' => $assessment->id,
                'course' => $assessment->course ? $assessment->course->title : 'N/A',
                'title' => $assessment->title,
                'scope' => $scope,
                'proficiency' => $proficiency,
                'status' => $assessment->status,
                'items' => $assessment->questions->count(),
                'questions' => $assessment->questions->map(function($q) {
                    return [
                        'id' => $q->id,
                        'text' => $q->question_text,
                        'points' => $q->points,
                        'image' => $q->image_path,
                        'options' => $q->options->map(function($o) {
                            return [
                                'id' => $o->id,
                                'text' => $o->option_text,
                                'is_correct' => $o->is_correct
                            ];
                        })
                    ];
                }),
                'skills' => $assessment->competencies->pluck('name')->join(', '),
                'description' => $assessment->description,
            ];
        });

        return view('learning.admin-assessments', compact('exams', 'courses', 'competencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'skills' => 'nullable|string', // Accept skills as a string
            'questions' => 'nullable|array'
        ]);

        return DB::transaction(function () use ($validated) {
            $assessment = Assessment::create([
                'course_id' => $validated['course_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'type' => $validated['type'],
                'status' => $validated['status'],
            ]);

            if (!empty($validated['skills'])) {
                $skillNames = array_map('trim', explode(',', $validated['skills']));
                $competencyIds = [];
                
                foreach ($skillNames as $name) {
                    if (empty($name)) continue;
                    
                    $competency = Competency::where('name', 'LIKE', $name)->first();
                    if ($competency) {
                        $competencyIds[] = $competency->id;
                    }
                }
                
                if (!empty($competencyIds)) {
                    $assessment->competencies()->sync($competencyIds);
                }
            }

            if (!empty($validated['questions'])) {
                foreach ($validated['questions'] as $index => $qData) {
                    $imagePath = null;
                    
                    // Handle file upload
                    if (isset($qData['image']) && $qData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $path = $qData['image']->store('assessment_images', 'public');
                        $imagePath = 'storage/' . $path;
                    }

                    $question = AssessmentQuestion::create([
                        'assessment_id' => $assessment->id,
                        'question_text' => $qData['text'],
                        'question_type' => $qData['type'] ?? 'multiple_choice',
                        'points' => $qData['points'] ?? 1,
                        'image_path' => $imagePath,
                        'order' => $index,
                    ]);

                    if (!empty($qData['options']) && is_array($qData['options'])) {
                        foreach ($qData['options'] as $optIndex => $optData) {
                            AssessmentOption::create([
                                'assessment_question_id' => $question->id,
                                'option_text' => $optData['text'],
                                'is_correct' => filter_var($optData['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                                'order' => $optIndex,
                            ]);
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'assessment' => $assessment]);
        });
    }

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->delete();
        return response()->json(['success' => true]);
    }

    public function scores()
    {
        $assessments = Assessment::with(['course', 'competencies', 'questions'])->get();

        $scoresData = $assessments->map(function ($assessment) {
            $totalPoints = $assessment->questions->sum('points');
            
            // Mock Data for statistics since result tables don't exist yet
            $participants = rand(10, 100);
            $passingRate = rand(60, 100);
            
            $scope = 'Internal';
            $proficiency = 'Beginner';
            if ($assessment->competencies->isNotEmpty()) {
                $scope = $assessment->competencies->first()->scope ?? 'Internal';
                $proficiency = $assessment->competencies->first()->proficiency ?? 'Beginner';
            }

            return [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'course_title' => $assessment->course ? $assessment->course->title : 'N/A',
                'course_picture' => $assessment->course ? $assessment->course->picture : null,
                'total_scores' => $totalPoints,
                'participants' => $participants,
                'passing_rate' => $passingRate,
                'proficiency' => $proficiency,
                'scope' => $scope,
                'skills' => $assessment->competencies->pluck('name')->toArray(),
            ];
        });

        return view('learning.admin-assessment-score', compact('scoresData'));
    }
}
