<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            $table->string('email')->unique();
            $table->string('password');

            $table->boolean('must_change_password')->default(true);
            $table->string('status')->default('active'); // 'active' or 'locked'
            $table->unsignedTinyInteger('failed_attempts')->default(0);

            $table->unsignedBigInteger('employee_id'); // link to external employee
            // You can add foreign key constraint if the employee table exists in this DB
            // $table->foreign('employee_id')->references('id')->on('employees');

            $table->timestamp('last_login_at')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};