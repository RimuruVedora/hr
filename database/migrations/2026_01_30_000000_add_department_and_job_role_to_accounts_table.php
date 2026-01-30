<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Add department_id foreign key
            if (!Schema::hasColumn('accounts', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            }

            // Add job_role_id foreign key (connecting positions to job_roles)
            if (!Schema::hasColumn('accounts', 'job_role_id')) {
                $table->foreignId('job_role_id')->nullable()->constrained('job_roles')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            
            if (Schema::hasColumn('accounts', 'job_role_id')) {
                $table->dropForeign(['job_role_id']);
                $table->dropColumn('job_role_id');
            }
        });
    }
};
