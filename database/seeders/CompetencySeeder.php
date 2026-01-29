<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competencies = [
            'Java Programming',
            'Python Programming',
            'Project Management',
            'Leadership',
            'Communication Skills',
            'Teamwork',
            'Problem Solving',
            'Data Analysis',
            'Cloud Computing',
            'Cybersecurity Awareness',
            'Agile Methodology',
            'Time Management',
            'Strategic Planning',
            'Customer Service',
            'Public Speaking'
        ];

        foreach ($competencies as $competency) {
            DB::table('competencies')->insertOrIgnore([
                'name' => $competency,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
