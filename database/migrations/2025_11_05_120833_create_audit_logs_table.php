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
     * Audit-Log: Wer hat wann was gemacht?
     * Nach Vorbild von Jira Audit-Log + GDPR-Compliance
     * 
     * Logged: Create, Update, Delete auf allen kritischen Ressourcen
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Wer?
            $table->string('action'); // create, update, delete, login, logout
            $table->string('resource_type'); // projects, employees, tasks, users, permissions
            $table->unsignedBigInteger('resource_id')->nullable(); // ID der betroffenen Ressource
            $table->json('old_values')->nullable(); // Alte Werte (nur bei update/delete)
            $table->json('new_values')->nullable(); // Neue Werte (nur bei create/update)
            $table->string('ip_address', 45)->nullable(); // IPv4/IPv6
            $table->text('user_agent')->nullable(); // Browser/Client Info
            $table->timestamp('created_at'); // Wann? (nur created_at, kein updated_at)
            
            // Indexes für schnelle Abfragen
            $table->index(['user_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index('created_at'); // Für Cleanup-Jobs (alte Logs löschen)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
