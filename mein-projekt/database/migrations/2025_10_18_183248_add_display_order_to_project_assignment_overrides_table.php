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
        Schema::table('project_assignment_overrides', function (Blueprint $table) {
            $table->unsignedInteger('display_order')->default(0)->after('source_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_assignment_overrides', function (Blueprint $table) {
            $table->dropColumn('display_order');
        });
    }
};
