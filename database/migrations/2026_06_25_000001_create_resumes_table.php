<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('skills')->nullable();
            $table->decimal('experience_years', 4, 1)->default(0.0);
            $table->json('experience_details')->nullable();
            $table->json('education')->nullable();
            $table->json('certifications')->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('current_salary')->nullable();
            $table->string('expected_salary')->nullable();
            $table->string('job_role')->nullable();
            $table->json('keywords')->nullable();
            $table->string('resume_path')->nullable();
            $table->json('ai_analysis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
