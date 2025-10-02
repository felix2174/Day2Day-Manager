<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('billable')->default(true)->after('moco_id');
            $table->boolean('fixed_price')->default(false)->after('billable');
            $table->boolean('retainer')->default(false)->after('fixed_price');
            $table->decimal('budget', 10, 2)->nullable()->after('retainer');
            $table->decimal('budget_monthly', 10, 2)->nullable()->after('budget');
            $table->decimal('budget_expenses', 10, 2)->default(0)->after('budget_monthly');
            $table->string('currency', 3)->default('EUR')->after('budget_expenses');
            $table->string('billing_variant')->nullable()->after('currency');
            $table->text('billing_address')->nullable()->after('billing_variant');
            $table->string('billing_email_to')->nullable()->after('billing_address');
            $table->string('billing_email_cc')->nullable()->after('billing_email_to');
            $table->text('billing_notes')->nullable()->after('billing_email_cc');
            $table->string('color', 7)->nullable()->after('billing_notes');
            $table->integer('customer_id')->nullable()->after('color');
            $table->integer('leader_id')->nullable()->after('customer_id');
            $table->integer('co_leader_id')->nullable()->after('leader_id');
            $table->integer('deal_id')->nullable()->after('co_leader_id');
            $table->integer('project_group_id')->nullable()->after('deal_id');
            $table->integer('billing_contact_id')->nullable()->after('project_group_id');
            $table->integer('contact_id')->nullable()->after('billing_contact_id');
            $table->integer('secondary_contact_id')->nullable()->after('contact_id');
            $table->timestamp('archived_on')->nullable()->after('secondary_contact_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'billable',
                'fixed_price',
                'retainer',
                'budget',
                'budget_monthly',
                'budget_expenses',
                'currency',
                'billing_variant',
                'billing_address',
                'billing_email_to',
                'billing_email_cc',
                'billing_notes',
                'color',
                'customer_id',
                'leader_id',
                'co_leader_id',
                'deal_id',
                'project_group_id',
                'billing_contact_id',
                'contact_id',
                'secondary_contact_id',
                'archived_on'
            ]);
        });
    }
};
