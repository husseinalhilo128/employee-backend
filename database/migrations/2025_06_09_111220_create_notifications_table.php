<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null = إشعار عام
            $table->string('title');    // عنوان الإشعار
            $table->text('body');       // النص الكامل للإشعار (بدلاً من message)
            $table->boolean('is_read')->default(false); // هل تم قراءته؟
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('notifications');
    }
};
