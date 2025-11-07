<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Füge employee_id zu users Tabelle hinzu (falls noch nicht vorhanden)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Prüfe ob Spalte bereits existiert
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('email');
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
                $table->index('employee_id');
            }
        });
    }

    /**
     * Rollback
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropForeign(['employee_id']);
                $table->dropIndex(['employee_id']);
                $table->dropColumn('employee_id');
            }
        });
    }
};


