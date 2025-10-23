<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedInteger('timeline_order')->default(0)->after('is_active');
        });

        $employees = \DB::table('employees')->orderBy('id')->get(['id']);
        foreach ($employees as $index => $employee) {
            \DB::table('employees')
                ->where('id', $employee->id)
                ->update(['timeline_order' => $index]);
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('timeline_order');
        });
    }
};


