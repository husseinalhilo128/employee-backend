<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['daily', 'time']); // يومية أو زمنية
            $table->date('date'); // تاريخ الإجازة (لبداية اليوم أو اليوم الزمني)
            $table->date('end_date')->nullable(); // فقط للإجازات اليومية لأكثر من يوم
            $table->time('start_time')->nullable(); // للإجازات الزمنية
            $table->time('end_time')->nullable(); // للإجازات الزمنية
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('leaves');
    }
};
