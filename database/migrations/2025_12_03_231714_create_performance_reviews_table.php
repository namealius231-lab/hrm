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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_assignment_id')->nullable()->constrained('performance_task_assignments')->nullOnDelete();
            $table->string('review_type')->default('task');
            $table->unsignedTinyInteger('rating')->default(0);
            $table->unsignedSmallInteger('efficiency_score')->default(0);
            $table->unsignedSmallInteger('quality_score')->default(0);
            $table->text('summary')->nullable();
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->date('review_period_start')->nullable();
            $table->date('review_period_end')->nullable();
            $table->text('ai_snapshot')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('employee_id');
            $table->index('reviewer_id');
            $table->index('review_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
