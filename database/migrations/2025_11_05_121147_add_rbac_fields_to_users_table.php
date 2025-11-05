<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Erweitere bestehende users Tabelle mit RBAC-Feldern
     * 
     * VORSICHT: employee_id und role_id existieren bereits!
     * Füge nur noch fehlende Felder hinzu.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Status & Tracking (NEUE Felder)
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            
            // Index für is_active (employee_id Index existiert bereits)
            $table->index('is_active');
        });
    }

    /**
     * Rollback: Entferne nur die NEU hinzugefügten Felder
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'is_active',
                'last_login_at',
                'last_login_ip'
            ]);
        });
    }
};
