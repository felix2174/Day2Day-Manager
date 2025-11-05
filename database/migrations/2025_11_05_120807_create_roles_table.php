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
     * Rollen: Admin, Management, Employee
     * Nach Vorbild von Jira/Linear: Hierarchische Struktur
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, management, employee
            $table->string('display_name'); // Admin, Geschäftsleitung, Mitarbeiter
            $table->text('description')->nullable();
            $table->integer('level')->default(0); // Hierarchie: 100=Admin, 50=Management, 10=Employee
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
