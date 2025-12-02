<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ticket #6: Aufgabenzeit in Minuten
 * 
 * Ändert weekly_hours von integer auf decimal(5,2),
 * um Dezimalstunden zu ermöglichen (z.B. 2.5 = 2h 30min)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Ändere von integer auf decimal für Dezimalstunden
            $table->decimal('weekly_hours', 5, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->integer('weekly_hours')->default(0)->change();
        });
    }
};
