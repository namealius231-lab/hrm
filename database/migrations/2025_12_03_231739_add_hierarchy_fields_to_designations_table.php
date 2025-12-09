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
        Schema::table('designations', function (Blueprint $table) {
            if (!Schema::hasColumn('designations', 'hierarchy_track')) {
                $table->string('hierarchy_track')->default('individual')->after('name');
            }
            if (!Schema::hasColumn('designations', 'hierarchy_level')) {
                $table->unsignedTinyInteger('hierarchy_level')->default(1)->after('hierarchy_track');
            }
            if (!Schema::hasColumn('designations', 'reports_to_designation_id')) {
                $table->unsignedBigInteger('reports_to_designation_id')->nullable()->after('hierarchy_level');
            }
            if (!Schema::hasColumn('designations', 'rank_weight')) {
                $table->unsignedInteger('rank_weight')->default(100)->after('reports_to_designation_id');
            }

            $table->index('hierarchy_level');
            $table->index('reports_to_designation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            if (Schema::hasColumn('designations', 'rank_weight')) {
                $table->dropColumn('rank_weight');
            }
            if (Schema::hasColumn('designations', 'reports_to_designation_id')) {
                $table->dropColumn('reports_to_designation_id');
            }
            if (Schema::hasColumn('designations', 'hierarchy_level')) {
                $table->dropColumn('hierarchy_level');
            }
            if (Schema::hasColumn('designations', 'hierarchy_track')) {
                $table->dropColumn('hierarchy_track');
            }
        });
    }
};
