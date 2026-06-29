<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('naukri_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('job_id');
            $table->unique(['user_id', 'job_id']);
            $table->string('title');
            $table->string('company');
            $table->string('location');
            $table->string('salary')->nullable();
            $table->string('experience_required');
            $table->text('description')->nullable();
            $table->string('posted_date')->nullable();
            $table->text('job_url');
            $table->integer('match_score')->default(0);
            $table->integer('skills_match_score')->default(0);
            $table->integer('experience_match_score')->default(0);
            $table->integer('location_match_score')->default(0);
            $table->enum('status', ['discovered', 'applied', 'duplicate', 'low_match', 'expired', 'failed'])->default('discovered');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->string('application_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('naukri_jobs');
    }
};
