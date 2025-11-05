<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Erweitert assignments-Tabelle mit:
     * - source: Tracking woher Assignment kommt (manual/moco_sync/responsible_fallback)
     * - role: Rolle im Projekt (project_lead/developer/designer/tester/team_member)
     * - is_active: Soft-Pause statt Löschen (true/false)
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Source Tracking (woher kommt die Zuweisung?)
            $table->string('source', 20)
                ->default('manual')
                ->after('priority_level')
                ->comment('Quelle: manual, moco_sync, responsible_fallback');

            // Rolle im Projekt
            $table->string('role', 50)
                ->default('team_member')
                ->after('task_name')
                ->comment('Rolle: project_lead, developer, designer, tester, team_member');

            // Soft-Pause (statt Delete)
            $table->boolean('is_active')
                ->default(true)
                ->after('end_date')
                ->comment('Aktiv = true, Pausiert = false');

            // Index für Performance
            $table->index(['is_active', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'source']);
            $table->dropColumn(['source', 'role', 'is_active']);
        });
    }
};
