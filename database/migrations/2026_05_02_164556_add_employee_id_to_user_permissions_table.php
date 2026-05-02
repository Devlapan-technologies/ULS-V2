<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->unsignedInteger('employee_id')->nullable()->after('user_id');
            $table->index('employee_id');
        });

        // Backfill employee_id from users table
        DB::statement('
            UPDATE user_permissions up
            JOIN users u ON u.id = up.user_id
            SET up.employee_id = u.employee_id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};