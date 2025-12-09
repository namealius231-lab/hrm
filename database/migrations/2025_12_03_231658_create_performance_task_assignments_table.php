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
        Schema::create('performance_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('performance_tasks')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedBigInteger('org_role_id')->nullable();
            $table->unsignedBigInteger('org_position_id')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('turnaround_minutes')->default(0);
            $table->text('last_progress_note')->nullable();
            $table->dateTime('last_progress_at')->nullable();
            $table->foreignId('last_activity_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->json('sla')->nullable();
            $table->timestamps();

            $table->unique(['task_id', 'employee_id']);
            $table->index('status');
            $table->index('employee_id');
            $table->index('org_role_id');
            $table->index('org_position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_task_assignments');
    }
};
