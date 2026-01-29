<?php

namespace App\Http\Controllers;

use App\Models\Training;
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
        } else {
            // Normal start: Check if start date has arrived
            if (now()->lt($training->start_date)) {
                 return redirect()->back()->with('error', 'Training cannot be started before the scheduled date.');
            }
        }
        
        $training->update(['status' => 'published']);
        
        return redirect()->back()->with('success', 'Training started successfully.');
    }
}
