<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Granulare Permissions nach Ressource + Aktion
     * Format: resource.action (projects.create, employees.delete, etc.)
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // projects.create, employees.edit
            $table->string('display_name'); // Projekte erstellen, Mitarbeiter bearbeiten
            $table->text('description')->nullable();
            $table->string('category'); // projects, employees, tasks, reports, system
            $table->timestamps();
            
            // Index für schnellere Permission-Checks
            $table->index(['category', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
