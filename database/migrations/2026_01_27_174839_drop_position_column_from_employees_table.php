<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrate existing data: Link employees to job_roles based on position name
        $employees = DB::table('employees')->whereNull('job_role_id')->whereNotNull('position')->get();
        
        foreach ($employees as $emp) {
            $role = DB::table('job_roles')->where('name', $emp->position)->first();
            if ($role) {
                DB::table('employees')
                    ->where('id', $emp->id)
                    ->update(['job_role_id' => $role->id]);
            }
        }

        // 2. Drop the column
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('position')->nullable()->after('department');
        });
        
        // Restore position strings from job_role_id
        $employees = DB::table('employees')->whereNotNull('job_role_id')->get();
        foreach ($employees as $emp) {
            $role = DB::table('job_roles')->where('id', $emp->job_role_id)->first();
            if ($role) {
                DB::table('employees')
                    ->where('id', $emp->id)
                    ->update(['position' => $role->name]);
            }
        }
    }
};
