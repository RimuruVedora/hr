<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Competency;
use App\Models\Employee;
use App\Models\JobRole;
use App\Models\EmployeeCompetency;

class CompetencyController extends Controller
{
    public function main()
    {
        $total = Competency::count();
        $orgWide = Competency::where('scope', 'Organization-wide')->count();

        return view('competency.competency-main-dashboard', [
            'stats' => [
                'total' => $total,
                'orgWide' => $orgWide,
            ],
        ]);
    }

    public function mapping()
    {
        return view('competency.competency-mapping');
    }

    public function analytics()
    {
        return view('competency.competency-gap-analytics');
    }

    public function indexJson()
    {
        $items = Competency::orderByDesc('id')->get();
        
        // Calculate Stats
        $total = $items->count();
        $orgWide = $items->where('scope', 'Organization-wide')->count();
        
        // Critical Gaps: Items with 'High' or 'Critical' weight
        // Assuming 'weight' stores string values like 'High', 'Medium', 'Low'
        $criticalGaps = $items->filter(function ($item) {
            return in_array(strtolower($item->weight), ['high', 'critical']);
        })->count();

        // Avg Proficiency: Map levels to 0-100 scale
        $proficiencyMap = [
            'beginner' => 25,
            'intermediate' => 50,
            'advanced' => 75,
            'expert' => 100
        ];

        $totalProficiency = $items->reduce(function ($carry, $item) use ($proficiencyMap) {
            $level = strtolower($item->proficiency);
            $score = $proficiencyMap[$level] ?? 0;
            return $carry + $score;
        }, 0);

        $avgProficiency = $total > 0 ? round($totalProficiency / $total, 1) : 0;

        return response()->json([
            'items' => $items->values(),
            'stats' => [
                'total' => $total,
                'orgWide' => $orgWide,
                'criticalGaps' => $criticalGaps,
                'avgProficiency' => $avgProficiency
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'scope' => 'required|string|max:255',
            'proficiency' => 'required|string|max:255',
            'weight' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);

        $item = Competency::create($data);
        return response()->json(['item' => $item], 201);
    }

    public function update(Request $request, $id)
    {
        $item = Competency::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'scope' => 'required|string|max:255',
            'proficiency' => 'required|string|max:255',
            'weight' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ]);
        $item->update($data);
        return response()->json(['item' => $item]);
    }

    public function destroy($id)
    {
        $item = Competency::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }

    public function analyticsData()
    {
        $employees = Employee::all(); 

        $data = $employees->map(function ($emp) {
            $empCompetencies = EmployeeCompetency::where('employee_id', $emp->id)->get();
            
            $roleName = $emp->position ?? 'Unknown';
            $jobRole = JobRole::where('name', $roleName)->with('competencies')->first();
            
            $compData = [];
            $roleCompetencies = $jobRole ? $jobRole->competencies : collect([]);
            
            if ($roleCompetencies->isEmpty()) {
                foreach ($empCompetencies as $ec) {
                     $compData[] = [
                        'name' => $ec->competency->name ?? 'Unknown',
                        'current' => $this->mapProficiencyToScore($ec->current_proficiency),
                        'target' => 0
                    ];
                }
            } else {
                foreach ($roleCompetencies as $rc) {
                    $empComp = $empCompetencies->firstWhere('competency_id', $rc->id);
                    $currentVal = $empComp ? $this->mapProficiencyToScore($empComp->current_proficiency) : 0;
                    $targetVal = $this->mapProficiencyToScore($rc->proficiency);
                    
                    $compData[] = [
                        'name' => $rc->name,
                        'current' => $currentVal,
                        'target' => $targetVal
                    ];
                }
            }
            
            $avgCurrent = count($compData) > 0 ? round(collect($compData)->avg('current'), 1) : 0;
            $avgRequired = count($compData) > 0 ? round(collect($compData)->avg('target'), 1) : 0;
            
            $labels = collect($compData)->pluck('name')->toArray();
            $currentSet = collect($compData)->pluck('current')->toArray();
            $requiredSet = collect($compData)->pluck('target')->toArray();
            
            // Priority Logic
            $gap = $avgRequired - $avgCurrent;
            $priority = 'Low';
            if ($gap > 2) $priority = 'Critical';
            elseif ($gap > 1) $priority = 'High';
            elseif ($gap > 0.5) $priority = 'Normal';
            
            return [
                'id' => $emp->employee_id,
                'name' => $emp->name, // Using name from Employee model
                'role' => $roleName,
                'dept' => $emp->department ?? 'General',
                'current' => $avgCurrent,
                'required' => $avgRequired,
                'priority' => $priority,
                'competencies' => $currentSet,
                'requiredSet' => $requiredSet,
                'labels' => $labels
            ];
        });

        return response()->json($data->values(), 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
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
