<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('identifier')->nullable()->after('moco_id');
            $table->date('finish_date')->nullable()->after('end_date');
            $table->text('info')->nullable()->after('description');
            $table->boolean('setting_include_time_report')->default(false)->after('billing_notes');
            $table->string('customer_report_url')->nullable()->after('setting_include_time_report');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'identifier',
                'finish_date',
                'info',
                'setting_include_time_report',
                'customer_report_url'
            ]);
        });
    }
};









