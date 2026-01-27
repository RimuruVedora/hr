<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobRole;

class JobRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Software Engineer',
                'description' => 'Develops and maintains software applications.',
                'weighting' => 3
            ],
            [
                'name' => 'HR Specialist',
                'description' => 'Manages recruitment and employee relations.',
                'weighting' => 2
            ],
            [
                'name' => 'Product Manager',
                'description' => 'Oversees product development and strategy.',
                'weighting' => 4
            ],
            [
                'name' => 'Data Analyst',
                'description' => 'Analyzes data to provide business insights.',
                'weighting' => 3
            ],
            [
                'name' => 'System Administrator',
                'description' => 'Maintains IT infrastructure and systems.',
                'weighting' => 3
            ]
        ];

        foreach ($roles as $role) {
            JobRole::create($role);
        }
    }
}
