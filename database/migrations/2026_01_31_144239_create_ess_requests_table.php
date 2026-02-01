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
        Schema::create('ess_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('request_category'); // employment, tax, attendance, financial, profile
            $table->string('request_type'); // specific type like 'coe', 'leave', etc.
            $table->json('details')->nullable(); // Stores dynamic fields (date, amount, reason, etc.)
            $table->string('status')->default('pending'); // pending, approved, rejected, processing
            $table->string('attachment_path')->nullable(); // For uploaded files
            $table->text('admin_remarks')->nullable(); // Comments from HR/Admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ess_requests');
    }
};
