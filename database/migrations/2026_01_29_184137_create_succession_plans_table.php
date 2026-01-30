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
        Schema::create('succession_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('target_role_id')->constrained('job_roles')->onDelete('cascade'); // The role they are being groomed for
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('status')->default('Pending'); // Pending, Active, Completed
            $table->string('readiness')->nullable(); // e.g., Ready Now, 1-2 Years
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('succession_plans');
    }
};
