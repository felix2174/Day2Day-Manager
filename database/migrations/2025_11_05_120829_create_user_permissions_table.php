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
     * Custom Permissions: Individuelle Rechte pro User
     * Override für Rolle (z.B. Employee bekommt zusätzlich "reports.export")
     * 
     * LOGIK:
     * - granted=true: User HAT diese Permission (zusätzlich zur Rolle)
     * - granted=false: User HAT NICHT diese Permission (trotz Rolle)
     */
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->boolean('granted')->default(true); // true=hat Recht, false=Recht entzogen
            $table->text('reason')->nullable(); // Warum wurde Recht gegeben/entzogen?
            $table->foreignId('granted_by')->nullable()->constrained('users'); // Wer hat Recht vergeben?
            $table->timestamps();
            
            // Unique: Ein User kann eine Permission nur 1x haben
            $table->unique(['user_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
