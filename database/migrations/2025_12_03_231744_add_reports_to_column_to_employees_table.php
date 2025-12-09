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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'reports_to_employee_id')) {
                $table->unsignedBigInteger('reports_to_employee_id')->nullable()->after('designation_id');
            }
            if (!Schema::hasColumn('employees', 'org_role_id')) {
                $table->unsignedBigInteger('org_role_id')->nullable()->after('reports_to_employee_id');
            }
            if (!Schema::hasColumn('employees', 'org_position_id')) {
                $table->unsignedBigInteger('org_position_id')->nullable()->after('org_role_id');
            }
            if (!Schema::hasColumn('employees', 'hierarchy_path')) {
                $table->json('hierarchy_path')->nullable()->after('org_position_id');
            }

            $table->index('reports_to_employee_id');
            $table->index('org_role_id');
            $table->index('org_position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'hierarchy_path')) {
                $table->dropColumn('hierarchy_path');
            }
            if (Schema::hasColumn('employees', 'org_position_id')) {
                $table->dropColumn('org_position_id');
            }
            if (Schema::hasColumn('employees', 'org_role_id')) {
                $table->dropColumn('org_role_id');
            }
            if (Schema::hasColumn('employees', 'reports_to_employee_id')) {
                $table->dropColumn('reports_to_employee_id');
            }
        });
    }
};
