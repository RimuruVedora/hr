<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\JobRole;
use App\Models\Competency;
use App\Models\EmployeeCompetency;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to prevent duplicates
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Employee::truncate();
        EmployeeCompetency::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = JobRole::all();
        $competencies = Competency::all();

        if ($competencies->isEmpty()) {
            $this->command->info('No competencies found. Please seed competencies first.');
            return;
        }

        $faker = \Faker\Factory::create();

        foreach ($roles as $index => $role) {
            $employee = Employee::create([
                'employee_id' => 'EMP' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'department' => 'IT',
                'position' => $role->name,
                'status' => 'Active',
                'date_hired' => now()->subMonths(rand(1, 24)),
            ]);

            // Assign all available competencies to the employee with random scores
            foreach ($competencies as $comp) {
                EmployeeCompetency::create([
                    'employee_id' => $employee->id,
                    'competency_id' => $comp->id,
                    'current_proficiency' => rand(1, 5), // Assuming 1-5 scale
                    'target_proficiency' => rand(3, 5),
                    'gap_score' => 0, // Will be calculated dynamically or updated later
                    'priority' => 'Normal',
                    'status' => 'Active',
                ]);
            }
        }
    }
}
