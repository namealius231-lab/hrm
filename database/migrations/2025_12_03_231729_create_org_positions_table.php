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
        Schema::create('org_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_role_id')->constrained('org_roles')->cascadeOnDelete();
            $table->foreignId('reports_to_position_id')->nullable()->constrained('org_positions')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->string('title');
            $table->string('code')->unique();
            $table->string('band')->nullable();
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('headcount')->default(1);
            $table->text('responsibilities')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('level');
            $table->index('department_id');
            $table->index('designation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_positions');
    }
};
