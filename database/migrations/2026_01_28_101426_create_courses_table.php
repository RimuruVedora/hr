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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('level', ['organization', 'department', 'management', 'team']);
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('category');
            $table->string('picture')->nullable();
            $table->string('material_pdf')->nullable();
            $table->string('duration');
            $table->text('description');
            $table->enum('status', ['published', 'draft'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
