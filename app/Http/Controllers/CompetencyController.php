<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Competency;

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
}
