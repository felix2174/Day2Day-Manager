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
        Schema::create('moco_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type'); // employees, projects, activities, all
            $table->string('status'); // started, completed, failed
            $table->integer('items_processed')->default(0);
            $table->integer('items_created')->default(0);
            $table->integer('items_updated')->default(0);
            $table->integer('items_skipped')->default(0);
            $table->text('error_message')->nullable();
            $table->json('parameters')->nullable(); // Sync parameters (active_only, date ranges, etc.)
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Who triggered the sync
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moco_sync_logs');
    }
};

