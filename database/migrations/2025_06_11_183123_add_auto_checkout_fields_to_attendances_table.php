<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'shift_type')) {
                $table->string('shift_type')->nullable()->after('worked_hours');
            }
            if (!Schema::hasColumn('attendances', 'extra_hours')) {
                $table->float('extra_hours')->default(0)->after('shift_type');
            }
            if (!Schema::hasColumn('attendances', 'missing_hours')) {
                $table->float('missing_hours')->default(0)->after('extra_hours');
            }
            if (!Schema::hasColumn('attendances', 'note')) {
                $table->text('note')->nullable()->after('branch_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['shift_type', 'extra_hours', 'missing_hours', 'note']);
        });
    }
};
