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
        Schema::table('assignments', function (Blueprint $table) {
            // Entferne alte Foreign Key Constraints falls vorhanden
            $table->dropForeign(['employee_id']);
            
            // Füge neue Constraint mit CASCADE DELETE hinzu
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Setze Foreign Key ohne CASCADE zurück
            $table->dropForeign(['employee_id']);
            
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees');
        });
    }
};
