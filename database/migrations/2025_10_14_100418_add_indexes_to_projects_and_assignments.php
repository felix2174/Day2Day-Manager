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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'moco_id')) {
                // nothing, column exists laut Migrationen; nur zur Sicherheit
            }
            $table->index('moco_id', 'projects_moco_id_index');
            $table->index('responsible_id', 'projects_responsible_id_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->index(['employee_id', 'project_id'], 'assignments_employee_project_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_moco_id_index');
            $table->dropIndex('projects_responsible_id_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_employee_project_index');
        });
    }
};
