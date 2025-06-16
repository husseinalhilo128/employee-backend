<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('monthly_reports', function (Blueprint $table) {
        if (!Schema::hasColumn('monthly_reports', 'total_present_days')) {
            $table->integer('total_present_days')->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'total_absent_days')) {
            $table->integer('total_absent_days')->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'total_leave_days')) {
            $table->integer('total_leave_days')->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'total_work_hours')) {
            $table->decimal('total_work_hours', 8, 2)->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'missing_hours')) {
            $table->decimal('missing_hours', 8, 2)->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'extra_hours')) {
            $table->decimal('extra_hours', 8, 2)->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'total_bonus')) {
            $table->decimal('total_bonus', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'total_deductions')) {
            $table->decimal('total_deductions', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('monthly_reports', 'final_salary')) {
            $table->decimal('final_salary', 10, 2)->default(0);
        }
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('monthly_reports', function (Blueprint $table) {
        $table->dropColumn([
            'total_present_days',
            'total_absent_days',
            'total_leave_days',
            'total_work_hours',
            'missing_hours',
            'extra_hours',
            'total_bonus',
            'total_deductions',
            'final_salary'
        ]);
    });
}
};
