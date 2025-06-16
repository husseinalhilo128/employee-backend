<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->float('worked_hours')->default(0);
            $table->string('shift_type')->nullable(); // ✅ تم تعديل النوع إلى string بدل enum
            $table->string('branch_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('note')->nullable(); // ملاحظة مثل "انصراف تلقائي"
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('attendances');
    }
};
