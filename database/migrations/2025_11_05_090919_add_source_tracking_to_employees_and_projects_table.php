<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'source' field to track data origin:
     * - 'manual': Created via UI by users (MUST NEVER be auto-deleted)
     * - 'moco': Synced from MOCO API (read-only, can be updated by sync)
     * - 'import': Test/dummy data (safe to delete via cleanup commands)
     */
    public function up(): void
    {
        // Add source field to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('source', ['manual', 'moco', 'import'])
                  ->default('manual')
                  ->after('moco_id')
                  ->comment('Data origin: manual=user-created, moco=API-synced, import=test-data');
            
            $table->index('source');
        });

        // Add source field to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('source', ['manual', 'moco', 'import'])
                  ->default('manual')
                  ->after('moco_id')
                  ->comment('Data origin: manual=user-created, moco=API-synced, import=test-data');
            
            $table->index('source');
        });

        // Backfill existing data with correct source
        // Rule: If moco_id exists → 'moco', otherwise → 'manual' (protect user data!)
        DB::table('employees')
            ->whereNotNull('moco_id')
            ->update(['source' => 'moco']);
        
        DB::table('employees')
            ->whereNull('moco_id')
            ->update(['source' => 'manual']);

        DB::table('projects')
            ->whereNotNull('moco_id')
            ->update(['source' => 'moco']);
        
        DB::table('projects')
            ->whereNull('moco_id')
            ->update(['source' => 'manual']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });
    }
};
