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
    public function employeeAssessments()
    {
        $user = auth()->user();
        
        // Ensure user has an employee record
        if (!$user->employee) {
            // Handle case where admin logs in or no employee record
            return view('learning.Employee-Assessment', ['courses' => []]);
        }

        $employee = $user->employee;
        $departmentId = $user->department_id; // Account has department_id

        // 0. Enrolled Courses (Priority)
        // Fetch trainings the user is participating in
        $enrolledTrainings = \App\Models\TrainingParticipant::where('employee_id', $employee->id)
            ->with(['training.course.competencies', 'training.assessment'])
            ->get()
            ->map(function ($participant) {
                $training = $participant->training;
                $course = $training->course;
                // Attach training info to course for formatting later
                $course->setAttribute('user_enrollment', $participant); 
                $course->setAttribute('user_training', $training);
                return $course;
            });

        // Helper to exclude courses with published trainings (for recommendations)
        // We don't want to recommend "Published" courses unless user is already enrolled (handled above)
        $excludePublished = function ($q) {
            $q->whereDoesntHave('trainings', function ($query) {
                $query->where('status', 'published');
            });
        };

        // 1. Department Courses
        $deptCourses = Course::where('department_id', $departmentId)
            ->where($excludePublished)
            ->with(['competencies', 'assessments'])
            ->get();

        // 2. Competency Gap Courses
        // Find competencies where current_proficiency < target_proficiency
        $gapCompetencyIds = DB::table('employee_competencies')
            ->where('employee_id', $employee->id)
            ->whereRaw('current_proficiency < target_proficiency')
            ->pluck('competency_id');

        $gapCourses = Course::whereHas('competencies', function ($q) use ($gapCompetencyIds) {
            $q->whereIn('competencies.id', $gapCompetencyIds);
        })
        ->where($excludePublished)
        ->with(['competencies', 'assessments'])
        ->get();

        // 3. Succession Planning Courses
        $successionCourses = collect();
        $successionPlan = \App\Models\SuccessionPlan::where('employee_id', $employee->id)
            ->where('status', '!=', 'Completed') // Assuming Active or Pending
            ->first();

        if ($successionPlan && $successionPlan->targetRole) {
            // Get competencies for the target role
            $targetCompetencyIds = DB::table('job_role_competency')
                ->where('job_role_id', $successionPlan->target_role_id)
                ->pluck('competency_id');
            
            $successionCourses = Course::whereHas('competencies', function ($q) use ($targetCompetencyIds) {
                $q->whereIn('competencies.id', $targetCompetencyIds);
            })
            ->where($excludePublished)
            ->with(['competencies', 'assessments'])
            ->get();
        }

        // Merge and Unique (Enrolled first to keep their data)
        $allCourses = $enrolledTrainings
            ->merge($deptCourses)
            ->merge($gapCourses)
            ->merge($successionCourses)
            ->unique('id');

        // Format for Frontend
        $formattedCourses = $allCourses->map(function ($course) {
            $isEnrolled = $course->getAttribute('user_enrollment') !== null;
            $training = $course->getAttribute('user_training');
            
            // Check for available pre-training if not enrolled
            $availableTraining = null;
            $isFull = false;

            if (!$isEnrolled) {
                // Find training that is pre-training (registration open)
                // We use startOfDay() to ensure trainings starting "today" are included regardless of current time
                $availableTraining = \App\Models\Training::where('course_id', $course->id)
                    ->where('status', 'pre_training')
                    ->where('start_date', '>=', now()->startOfDay()) 
                    ->withCount('participants')
                    ->first();
                
                if ($availableTraining) {
                    $isFull = $availableTraining->participants_count >= $availableTraining->capacity;
                }
            }

            $examAccess = false;
            if ($isEnrolled && $training) {
                $now = now();
                if ($training->status === 'published' && $now->between($training->start_date, $training->end_date)) {
                    $examAccess = true;
                }
            }

            // Resolve Assessment Data
            $assessment = null;
            if ($training && $training->assessment) {
                $assessment = $training->assessment;
            } elseif ($course->assessments && $course->assessments->isNotEmpty()) {
                $assessment = $course->assessments->first();
            }

            $examData = [
                'title' => $assessment ? $assessment->title : 'Final Exam',
                'items' => $assessment ? $assessment->questions()->count() : 0,
                'type' => $assessment ? $assessment->type : 'Online',
                'duration' => $assessment ? ($assessment->time_limit . ' Mins') : '60 Mins',
            ];

            return [
                'id' => $course->id,
                'title' => $course->title,
                'category' => $course->category ?? 'General',
                'description' => $course->description,
                'date' => $course->created_at->format('M d, Y'),
                'duration' => $course->duration,
                'skills' => $course->competencies->pluck('name')->toArray(),
                'enrolled' => $isEnrolled,
                'training_id' => $training ? $training->id : null,
                'training_status' => $training ? $training->status : null,
                'start_date' => $training ? $training->start_date->format('M d, Y H:i') : null,
                'end_date' => $training ? $training->end_date->format('M d, Y H:i') : null,
                'exam_access' => $examAccess,
                'has_schedule' => !!$availableTraining, // True if there is a schedule available to enroll
                'is_full' => $isFull,
                'available_training_id' => $availableTraining ? $availableTraining->id : null,
                'materials' => [
                    ['title' => 'Course PDF', 'link' => $course->material_pdf ? asset('storage/' . $course->material_pdf) : '#'],
                ],
                'exam' => $examData
            ];
        })->values(); // Reset keys for JSON array

        return view('learning.Employee-Assessment', ['courses' => $formattedCourses]);
    }

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
