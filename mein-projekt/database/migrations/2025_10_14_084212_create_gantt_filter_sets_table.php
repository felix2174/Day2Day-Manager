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
        Schema::create('gantt_filter_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Name des Filter-Sets
            $table->text('description')->nullable(); // Beschreibung des Filter-Sets
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Benutzer-spezifische Filter-Sets
            $table->json('filters'); // JSON mit allen Filter-Einstellungen
            $table->boolean('is_default')->default(false); // Standard-Filter-Set
            $table->timestamps();
            
            // Index für bessere Performance
            $table->index(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gantt_filter_sets');
    }
};
