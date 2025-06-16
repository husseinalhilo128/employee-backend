<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('employee'); // employee, admin, delivery
            $table->string('profile_image')->nullable();
            $table->integer('base_salary')->default(0); // الراتب الأساسي
            $table->integer('allowed_absence_days')->default(0);
            $table->time('morning_start')->nullable();
            $table->time('morning_end')->nullable();
            $table->float('morning_hours')->nullable();
            $table->time('evening_start')->nullable();
            $table->time('evening_end')->nullable();
            $table->float('evening_hours')->nullable();
            $table->float('double_shift_hours')->nullable();
            $table->integer('delay_allowance_minutes')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};
