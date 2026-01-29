<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeCompetency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CompetencyAIService
{
    /**
     * Generate a real AI development plan based on competency gaps using Gemini API.
     */
    public function generateDevelopmentPlan($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $competencies = EmployeeCompetency::where('employee_id', $employeeId)
            ->with('competency')
            ->get();

        $profile = [];
        foreach ($competencies as $comp) {
            $gap = $comp->target_proficiency - $comp->current_proficiency;
            $status = $gap > 0 ? 'Gap' : ($gap == 0 ? 'Meets Expectations' : 'Exceeds Expectations');
            
            $profile[] = [
                'name' => $comp->competency->name ?? 'Unknown Skill',
                'current' => $this->getLevelName($comp->current_proficiency),
                'target' => $this->getLevelName($comp->target_proficiency),
                'status' => $status,
                'gap_score' => $gap
            ];
        }

        if (empty($profile)) {
            return [
                'summary' => "No competency data available for {$employee->name}.",
                'actions' => []
            ];
        }

        // Check Cache
        $profileHash = md5(json_encode($profile));
        $cacheKey = "ai_plan_v2_{$employeeId}_{$profileHash}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->callGeminiAPI($employee, $profile);

        // Only cache if it's a valid plan (not a fallback)
        if (isset($result['summary']) && empty($result['is_fallback'])) {
            Cache::put($cacheKey, $result, now()->addDays(7));
        }

        return $result;
    }

    public function chatWithPlan($employeeId, $userMessage)
    {
        // 1. Retrieve the Context (Plan)
        $plan = $this->generateDevelopmentPlan($employeeId);
        $employee = Employee::findOrFail($employeeId);
        $roleName = $employee->jobRole ? $employee->jobRole->name : 'Unknown Role';

        // 2. Construct the Chat Prompt
        $prompt = "You are an expert HR L&D specialist discussing a development plan for {$employee->name} ({$roleName}).
        
        Current Development Plan Context:
        Summary: " . ($plan['summary'] ?? 'N/A') . "
        Actions: " . json_encode($plan['actions'] ?? []) . "
        
        User Question: \"{$userMessage}\"
        
        Provide a helpful, professional, and encouraging response explaining the details, reasoning, or resources related to the plan. Keep it concise but informative. Do not use JSON output here, just plain text.";

        // 3. Call Gemini
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return "I'm sorry, I cannot answer right now (API Key missing).";
        }

        $models = ['gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.5-flash', 'gemini-flash-latest'];

        foreach ($models as $model) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json'
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if ($text) return $text;
                }
            } catch (\Exception $e) {
                Log::error("Gemini Chat Exception ({$model}): " . $e->getMessage());
            }
        }

        Log::error("Gemini Chat Failed: All models failed or returned errors.");
        return "I'm sorry, I'm having trouble connecting to the AI right now. Please try again later.";
    }

    private function callGeminiAPI($employee, $profile)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return $this->fallbackPlan($employee, $profile, "API Key missing");
        }

        $roleName = $employee->jobRole ? $employee->jobRole->name : 'Unknown Role';
        $prompt = "You are an expert HR L&D specialist. Create a comprehensive, holistic development plan for employee '{$employee->name}' (Role: {$roleName}).
        
        Full Competency Profile (Current Proficiency vs Role Target):
        " . json_encode($profile) . "

        Requirements:
        1. Analyze the ENTIRE profile. Don't just list gaps; explain the *patterns* (e.g., 'Strong technical foundation but lacks strategic oversight').
        2. Provide your professional opinion on *why* certain gaps might exist given the context of their other skills.
        3. For GAPS: Provide specific, **searchable** learning resources (official certifications, standard methodologies, well-known books/courses).
        4. For STRENGTHS: Suggest how to leverage them (mentorship, leading specific initiatives).
        5. Output STRICTLY valid JSON with no markdown code blocks.
        
        Expected JSON Structure:
        {
          \"summary\": \"Detailed analysis of the employee's overall profile, strengths, and areas for growth.\",
          \"actions\": [
            {
              \"skill\": \"Skill Name\",
              \"gap\": \"Current -> Target (or Strength)\",
              \"suggestions\": [\"Specific Action 1\", \"Specific Action 2\"]
            }
          ]
        }";

        $models = ['gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.5-flash', 'gemini-flash-latest'];

        foreach ($models as $model) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json'
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    // Clean markdown if present
                    $text = str_replace(['```json', '```'], '', $text);
                    
                    $json = json_decode($text, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $json;
                    }
                }

                if ($response->status() === 429) {
                    Log::warning("Gemini API Rate Limit Exceeded ({$model}) for employee " . $employee->id);
                    // If rate limited, don't try other models as they likely share quota? 
                    // Actually, sometimes different models have different quotas. Let's continue.
                    continue; 
                }
                
                Log::warning("Gemini API Error ({$model}): " . $response->body());

            } catch (\Exception $e) {
                Log::error("Gemini Exception ({$model}): " . $e->getMessage());
            }
        }

        return $this->fallbackPlan($employee, $profile, "All AI models failed");
    }

    private function fallbackPlan($employee, $profile, $reason)
    {
        // Fallback to heuristic logic if API fails
        // We keep the summary clean for the user, but log the reason.
        
        $gapsCount = count(array_filter($profile, fn($p) => $p['gap_score'] > 0));
        
        $plan = [
            'summary' => "{$employee->name} has {$gapsCount} competency gaps identified in their profile.",
            'actions' => [],
            'is_fallback' => true
        ];

        foreach ($profile as $item) {
            $plan['actions'][] = $this->suggestAction($item);
        }

        return $plan;
    }

    private function getLevelName($score)
    {
        if ($score <= 1) return 'Beginner';
        if ($score <= 2) return 'Intermediate';
        if ($score <= 3) return 'Advanced';
        if ($score <= 4) return 'Expert';
        return 'Master';
    }

    private function suggestAction($item)
    {
        $skill = $item['name'];
        $diff = $item['gap_score'];

        $action = [
            'skill' => $skill,
            'gap' => "{$item['current']} → {$item['target']}",
            'suggestions' => []
        ];

        if ($diff > 0) {
            // Gap Actions
            if ($diff >= 2) {
                $action['suggestions'][] = "Enroll in a comprehensive certification program or advanced workshop specifically for {$skill}.";
                $action['suggestions'][] = "Pair with a senior mentor (Expert/Master level) for weekly code reviews and guidance on {$skill}.";
                $action['suggestions'][] = "Lead a small project or module that heavily relies on {$skill} to build practical expertise.";
            } else {
                $action['suggestions'][] = "Engage in targeted self-paced learning (e.g., Udemy, Coursera) to bridge the {$skill} gap.";
                $action['suggestions'][] = "Participate in peer programming sessions focusing on {$skill} best practices.";
                $action['suggestions'][] = "Take on specific tickets or tasks that require applying {$skill} in a real-world context.";
            }
        } else {
            // Strength / Maintenance Actions
            $action['suggestions'][] = "Maintain proficiency by staying updated with latest {$skill} trends.";
            $action['suggestions'][] = "Mentor junior colleagues who are developing their {$skill} competencies.";
        }

        return $action;
    }
}
