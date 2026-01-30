<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingParticipant;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\TrainingStartOtp;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    public function index()
    {
        // Auto-complete trainings that have passed their end date
        Training::where('status', '!=', 'completed')
            ->where('end_date', '<', now())
            ->update(['status' => 'completed']);

        $user = Auth::user();

        // Employee Logic (Account_Type 2)
        if ($user->Account_Type == 2) {
            $employee = $user->employee;
            
            if (!$employee) {
                 return redirect()->route('home')->with('error', 'Employee record not found.');
            }
    
            // Fetch all trainings for this employee
            $participations = TrainingParticipant::where('employee_id', $employee->id)
                ->with(['training.course'])
                ->get();
                
            $myTrainings = $participations->map(function($p) {
                return $p->training;
            })->filter();
    
            $now = now();
            
            // 1. Active Training Schedule (Ongoing or Soon)
            $activeCount = $myTrainings->filter(function($t) use ($now) {
                return $t->end_date >= $now;
            })->count();
            
            // 2. Training Attended (Past)
            $attendedCount = $myTrainings->filter(function($t) use ($now) {
                 return $t->end_date < $now;
            })->count();
            
            // 3. Course Enrolled
            $enrolledCoursesCount = $myTrainings->pluck('course_id')->unique()->count();
    
            return view('training.Employee-Training', compact('myTrainings', 'activeCount', 'attendedCount', 'enrolledCoursesCount'));
        }

        // Admin Logic (Default or Account_Type 1)
        $publishedTrainings = Training::where('status', 'published')
            ->with(['course.competencies', 'participants'])
            ->get();
            
        $preTrainings = Training::where('status', 'pre_training')
            ->with(['course.competencies', 'participants'])
            ->get();
            
        $courses = Course::with(['competencies', 'assessments' => function($query) {
            $query->where('status', 'published');
        }])->where('status', 'published')->get();

        // Mock Analytics Data
        $analytics = $publishedTrainings->map(function($training) {
            return [
                'title' => $training->title,
                'participants' => $training->participants->count(), // Real count if participants exist
                'capacity' => $training->capacity,
                'duration' => $training->duration,
                'progress' => rand(10, 90), // Mock progress for now
            ];
        });

        return view('training.admin-training', compact('publishedTrainings', 'preTrainings', 'courses', 'analytics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'capacity' => 'required|integer|min:1',
            'duration_value' => 'required|numeric|min:1',
            'duration_unit' => 'required|string|in:Hours,Days',
            'org_scope' => 'required|string',
            'proficiency' => 'required|string',
            'description' => 'nullable|string',
            'training_type' => 'required|string|in:physical,online_exam,both',
            'assessment_id' => 'nullable|exists:assessments,id|required_if:training_type,online_exam,both',
            'location' => 'nullable|string|max:255',
        ]);

        $data = $validated;
        $data['duration'] = $request->duration_value . ' ' . $request->duration_unit;
        unset($data['duration_value'], $data['duration_unit']);

        // Default status is pre_training from migration default
        Training::create($data);

        return redirect()->route('training.schedule')->with('success', 'Training scheduled successfully.');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'training_id' => 'required|exists:trainings,id',
        ]);

        $training = Training::findOrFail($request->training_id);
        $user = Auth::user();
        
        // Generate OTP
        $otp = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        
        // Store in Cache for 10 minutes
        $key = 'training_start_otp_' . $user->id . '_' . $training->id;
        Cache::put($key, $otp, now()->addMinutes(10));
        
        // Send Email
        try {
            Mail::to($user->email)->send(new TrainingStartOtp($otp, $training->title));
            return response()->json(['success' => true, 'message' => 'OTP sent successfully to ' . $user->email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP: ' . $e->getMessage()], 500);
        }
    }

    public function start(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        
        // Check if it's an immediate start request (with OTP)
        if ($request->filled('otp')) {
            $user = Auth::user();
            $key = 'training_start_otp_' . $user->id . '_' . $training->id;
            $cachedOtp = Cache::get($key);
            
            if (!$cachedOtp || $cachedOtp !== strtoupper($request->otp)) {
                 return redirect()->back()->with('error', 'Invalid or expired OTP.');
            }
            
            // Clear OTP after successful use
            Cache::forget($key);

            // Update start_date if it is in the future so that exams become accessible immediately
            if ($training->start_date->isFuture()) {
                $training->start_date = now();
                $training->save();
            }
        } else {
            // Normal start: Check if start date has arrived
            if (now()->lt($training->start_date)) {
                 return redirect()->back()->with('error', 'Training cannot be started before the scheduled date.');
            }
        }
        
        $training->update(['status' => 'published']);
        
        return redirect()->back()->with('success', 'Training started successfully.');
    }

    public function enroll($id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        $employee = $user->employee; 

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
        }

        // Check if already enrolled
        $exists = TrainingParticipant::where('training_id', $training->id)
            ->where('employee_id', $employee->id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Already enrolled.']);
        }

        // Check capacity
        if ($training->participants()->count() >= $training->capacity) {
             return response()->json(['success' => false, 'message' => 'Class is full.']);
        }

        TrainingParticipant::create([
            'training_id' => $training->id,
            'employee_id' => $employee->id,
            'status' => 'enrolled'
        ]);

        return response()->json(['success' => true, 'message' => 'Enrolled successfully.']);
    }

    public function evaluation()
    {
        $trainings = Training::whereIn('status', ['published', 'in_progress'])
            ->withCount('participants')
            ->get();
            
        return view('training.admin-training-evaluation', compact('trainings'));
    }

    public function getParticipants($trainingId)
    {
        $participants = TrainingParticipant::where('training_id', $trainingId)
            ->with(['employee'])
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'employee_name' => $p->employee->first_name . ' ' . $p->employee->last_name,
                    'employee_id' => $p->employee->employee_id,
                    'department' => $p->employee->department,
                    'grade' => $p->grade,
                    'remarks' => $p->remarks,
                    'status' => $p->status
                ];
            });
            
        return response()->json($participants);
    }

    public function updateGrade(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:training_participants,id',
            'grade' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $participant = TrainingParticipant::findOrFail($request->participant_id);
        $participant->update([
            'grade' => $request->grade,
            'remarks' => $request->remarks,
        ]);

        return response()->json(['success' => true, 'message' => 'Grade updated successfully.']);
    }
}
