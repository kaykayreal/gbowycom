<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salCustomers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('account_number')->unique();
            $table->string('bvn')->unique();
            $table->string('bank');
            $table->decimal('average_salary', 10, 2)->nullable();
            $table->integer('existing_loan')->nullable();
            $table->integer('tenure')->nullable();
            $table->string('status')->default('pending'); // pending, approved, suspended
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salCustomers');
    }
};
