<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('weighting')->default(0); // 1-5 scale
            $table->timestamps();
        });

        Schema::create('job_role_competency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_role_id')->constrained('job_roles')->onDelete('cascade');
            $table->foreignId('competency_id')->constrained('competencies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_role_competency');
        Schema::dropIfExists('job_roles');
    }
};
