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
        Schema::create('org_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('reports_to_role_id')->nullable()->constrained('org_roles')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('rank_weight')->default(100);
            $table->boolean('is_executive')->default(false);
            $table->text('responsibilities')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('level');
            $table->index('rank_weight');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_roles');
    }
};
