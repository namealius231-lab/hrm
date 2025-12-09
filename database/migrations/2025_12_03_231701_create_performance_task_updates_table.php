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
        Schema::create('performance_task_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_assignment_id')->constrained('performance_task_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('summary')->nullable();
            $table->text('strategy')->nullable();
            $table->text('blockers')->nullable();
            $table->string('evidence_path')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('task_assignment_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_task_updates');
    }
};
