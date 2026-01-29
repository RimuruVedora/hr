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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('capacity');
            $table->string('duration'); // e.g., "2 hours", "3 days"
            $table->string('org_scope'); // e.g., "Internal", "Departmental", "Public"
            $table->string('proficiency'); // e.g., "Beginner", "Intermediate", "Advanced"
            $table->text('description')->nullable();
            $table->string('status')->default('pre_training'); // pre_training, published, ongoing, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
