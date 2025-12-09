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
        Schema::create('employee_position_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('org_position_id')->constrained('org_positions')->cascadeOnDelete();
            $table->foreignId('org_role_id')->nullable()->constrained('org_roles')->nullOnDelete();
            $table->foreignId('reports_to_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->string('status')->default('active');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('status');
            $table->index('effective_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_position_assignments');
    }
};
