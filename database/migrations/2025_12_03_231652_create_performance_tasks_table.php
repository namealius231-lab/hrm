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
        Schema::create('performance_tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('difficulty')->default('medium');
            $table->string('priority')->default('normal');
            $table->string('status')->default('draft');
            $table->string('visibility')->default('private');
            $table->boolean('auto_overdue')->default(true);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->unsignedInteger('expected_hours')->default(0);
            $table->unsignedInteger('buffer_hours')->default(0);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->text('ai_summary')->nullable();
            $table->timestamp('ai_summary_generated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('deadline');
            $table->index(['created_by', 'assigned_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_tasks');
    }
};
