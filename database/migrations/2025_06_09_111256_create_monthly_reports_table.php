<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->integer('base_salary')->default(0);
            $table->integer('total_days_count')->default(0);
            $table->integer('attendance_days')->default(0);
            $table->integer('absence_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->integer('bonus_amount')->default(0);
            $table->integer('deduction_amount')->default(0);
            $table->integer('final_salary')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('monthly_reports');
    }
};
